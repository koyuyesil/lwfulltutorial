<?php

namespace App\Models;

use App\Enums\PriorityType;
use App\Enums\TicketStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class Ticket extends Model
{
    /** @use HasFactory<\Database\Factories\TicketFactory> */
    use HasFactory;

    /** Önceliğe göre hedef teslim süresi (saat). */
    public const SLA_HOURS = [
        'high' => 24,
        'normal' => 72,
        'low' => 120,
    ];

    /** Kalan süre toplam sürenin bu oranının altına inince "riskli" sayılır. */
    public const SLA_WARNING_RATIO = 0.25;

    protected $fillable = [
        'user_id', 'client_product_id', 'problem', 'priority', 'status', 'due_at', 'technician',
        'intake', 'lock_type', 'lock_secret', 'signature', 'signed_at',
    ];

    protected $hidden = ['lock_secret', 'signature'];

    // Veritabanından gelen verileri tipine dönüştür
    protected $casts = [
        'status' => TicketStatus::class,
        'priority' => PriorityType::class,
        'due_at' => 'datetime',
        'status_changed_at' => 'datetime',
        'closed_at' => 'datetime',
        'intake' => 'array',
        'lock_secret' => 'encrypted',
        'signed_at' => 'datetime',
        'quote_total' => 'decimal:2',
        'quote_sent_at' => 'datetime',
        'quote_decided_at' => 'datetime',
        'survey_score' => 'integer',
        'survey_at' => 'datetime',
    ];

    public const QUOTE_PENDING = 'pending';

    public const QUOTE_APPROVED = 'approved';

    public const QUOTE_REJECTED = 'rejected';

    /** Kabulde işaretlenen aksesuarlar ve dış durum maddeleri */
    public const ACCESSORIES = ['Şarj aleti', 'Kablo', 'Kılıf', 'SIM kart', 'Hafıza kartı', 'Kutu', 'Kalem', 'Kulaklık'];

    public const CONDITIONS = ['Ekran çizik', 'Ekran kırık', 'Kasa ezik / çizik', 'Arka kapak çatlak', 'Kamera camı kırık', 'Sıvı teması izi', 'Tuş eksik / basmıyor', 'Cihaz açılmıyor (test edilemedi)'];

    protected static function booted(): void
    {
        static::creating(function (Ticket $ticket) {
            $ticket->tracking_code ??= self::generateTrackingCode();
            $ticket->status ??= TicketStatus::RECEIVED;
            $ticket->status_changed_at ??= now();
            $ticket->due_at ??= now()->addHours(self::slaHoursFor($ticket->priority, User::find($ticket->user_id)));
        });

        static::created(function (Ticket $ticket) {
            $ticket->events()->create([
                'user_id' => Auth::id() ?? $ticket->user_id,
                'type' => TicketEvent::TYPE_CREATED,
                'to_status' => $ticket->status,
                'is_public' => true,
            ]);
        });
    }

    /**
     * Müşteriye verilen, tahmin edilemeyen takip kodu (örn. KM-7XQ4P9TA).
     * Karışabilecek karakterler (0/O, 1/I/L) kullanılmaz.
     */
    public static function generateTrackingCode(): string
    {
        $alphabet = '23456789ABCDEFGHJKMNPQRSTUVWXYZ';

        do {
            $code = 'KM-';
            for ($i = 0; $i < 8; $i++) {
                $code .= $alphabet[random_int(0, strlen($alphabet) - 1)];
            }
        } while (static::where('tracking_code', $code)->exists());

        return $code;
    }

    /** Kullanıcının yazdığı kodu normalize eder (boşluk, küçük harf, tire eksikliği). */
    public static function normalizeTrackingCode(string $input): string
    {
        $clean = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $input));

        return str_starts_with($clean, 'KM') ? 'KM-'.substr($clean, 2) : 'KM-'.$clean;
    }

    /** Firmanın ayarladığı SLA süresi; ayar yoksa varsayılan. */
    public static function slaHoursFor(PriorityType|string|null $priority, ?User $user = null): int
    {
        $key = $priority instanceof PriorityType ? $priority->value : (string) $priority;
        $hours = self::slaTable($user ?? Auth::user());

        return (int) ($hours[$key] ?? $hours['normal'] ?? self::SLA_HOURS['normal']);
    }

    /** @return array<string, int> */
    public static function slaTable(?User $user): array
    {
        return $user ? array_map('intval', (array) $user->setting('tickets.sla_hours', self::SLA_HOURS)) : self::SLA_HOURS;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function clientProduct()
    {
        return $this->belongsTo(ClientProduct::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(TicketEvent::class)->latest('id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(TicketItem::class)->orderBy('id');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(TicketPhoto::class)->orderBy('id');
    }

    /* ------------------------------------------------------------------
     | Teklif ve müşteri onayı
     * ----------------------------------------------------------------- */

    public function itemsTotal(): float
    {
        return (float) $this->items->sum(fn (TicketItem $i) => $i->total());
    }

    public function itemsCost(): float
    {
        return (float) $this->items->sum(fn (TicketItem $i) => $i->costTotal());
    }

    /** Teklifi müşteriye açar; teşhisteyse "Onay bekliyor"a geçer. */
    public function sendQuote(): void
    {
        DB::transaction(function () {
            $total = $this->loadMissing('items')->itemsTotal();
            $this->forceFill([
                'quote_status' => self::QUOTE_PENDING,
                'quote_total' => $total,
                'quote_sent_at' => now(),
                'quote_decided_at' => null,
                'quote_decided_by' => null,
                'quote_decision_ip' => null,
                'quote_customer_note' => null,
            ])->save();

            $note = 'Onarım teklifi gönderildi: '.self::money($total).'. Takip sayfasından onaylayabilirsiniz.';
            if ($this->status->canTransitionTo(TicketStatus::AWAITING_APPROVAL)) {
                $this->transitionTo(TicketStatus::AWAITING_APPROVAL, $note);
            } else {
                $this->events()->create(['user_id' => Auth::id(), 'type' => TicketEvent::TYPE_QUOTE, 'note' => $note, 'is_public' => true]);
            }
        });
    }

    /**
     * Teklif kararı. by: customer (takip sayfası) | staff (telefonla onay vb.)
     * Onayda "Onay bekliyor" durumundaki iş onarıma geçer.
     */
    public function decideQuote(bool $approved, string $by = 'customer', ?string $note = null, ?string $ip = null): void
    {
        if ($this->quote_status !== self::QUOTE_PENDING) {
            throw new InvalidArgumentException('Bu teklif için karar zaten verilmiş.');
        }

        DB::transaction(function () use ($approved, $by, $note, $ip) {
            $this->forceFill([
                'quote_status' => $approved ? self::QUOTE_APPROVED : self::QUOTE_REJECTED,
                'quote_decided_at' => now(),
                'quote_decided_by' => $by,
                'quote_decision_ip' => $ip,
                'quote_customer_note' => $note ?: null,
            ])->save();

            $who = $by === 'customer' ? 'Müşteri' : 'Servis (müşteri adına)';
            $text = $who.' teklifi '.($approved ? 'onayladı' : 'reddetti').' ('.self::money((float) $this->quote_total).').'
                .($note ? "\nNot: ".$note : '');

            if ($approved && $this->status === TicketStatus::AWAITING_APPROVAL) {
                $this->transitionTo(TicketStatus::IN_REPAIR, $text);
            } else {
                $this->events()->create(['user_id' => Auth::id(), 'type' => TicketEvent::TYPE_QUOTE, 'note' => $text, 'is_public' => true]);
                $this->touch();
            }
        });
    }

    public static function money(float $amount): string
    {
        return '₺'.number_format($amount, 2, ',', '.');
    }

    /* ------------------------------------------------------------------
     | Cihaz geçmişi (IMEI / seri)
     * ----------------------------------------------------------------- */

    /** Aynı cihazın (aynı kayıt ya da aynı IMEI / seri) bu servisteki diğer kayıtları. */
    public function deviceHistory()
    {
        $device = $this->clientProduct;
        $deviceIds = ClientProduct::query()
            ->where('user_id', $this->user_id)
            ->where(fn ($q) => $q->whereKey($device->id)
                ->when($device->imei, fn ($q) => $q->orWhere('imei', $device->imei))
                ->when($device->serial, fn ($q) => $q->orWhere('serial', $device->serial)))
            ->pluck('id');

        return self::query()
            ->whereIn('client_product_id', $deviceIds)
            ->whereKeyNot($this->id)
            ->with('clientProduct.client')
            ->latest('id')
            ->get();
    }

    /* ------------------------------------------------------------------
     | Memnuniyet anketi
     * ----------------------------------------------------------------- */

    /** Müşteriye gönderilen imzalı anket bağlantısı (60 gün geçerli). */
    public function surveyUrl(): string
    {
        return \Illuminate\Support\Facades\URL::temporarySignedRoute('survey', now()->addDays(60), ['ticket' => $this->tracking_code]);
    }

    public function trackingUrl(): string
    {
        return route('tracking', ['kod' => $this->tracking_code]);
    }

    /**
     * İş akışı kurallarına uyarak durumu değiştirir ve olayı kaydeder.
     *
     * @throws InvalidArgumentException izin verilmeyen geçişte
     */
    public function transitionTo(TicketStatus $to, ?string $note = null, bool $public = true): void
    {
        $from = $this->status;

        if (! $from->canTransitionTo($to)) {
            throw new InvalidArgumentException("{$from->label()} → {$to->label()} geçişine izin verilmiyor.");
        }

        DB::transaction(function () use ($from, $to, $note, $public) {
            $this->forceFill([
                'status' => $to,
                'status_changed_at' => now(),
                'closed_at' => $to->isClosed() ? now() : null,
            ])->save();

            $this->events()->create([
                'user_id' => Auth::id(),
                'type' => TicketEvent::TYPE_STATUS_CHANGED,
                'from_status' => $from,
                'to_status' => $to,
                'note' => $note ?: null,
                'is_public' => $public,
            ]);
        });
    }

    public function addNote(string $note, bool $public = false): TicketEvent
    {
        $event = $this->events()->create([
            'user_id' => Auth::id(),
            'type' => TicketEvent::TYPE_NOTE,
            'note' => $note,
            'is_public' => $public,
        ]);

        $this->touch();

        return $event;
    }

    /* ------------------------------------------------------------------
     | SLA
     * ----------------------------------------------------------------- */

    /** ok | warning | overdue | closed | none */
    public function slaState(): string
    {
        if ($this->status->isClosed()) {
            return 'closed';
        }

        if (! $this->due_at) {
            return 'none';
        }

        if ($this->due_at->isPast()) {
            return 'overdue';
        }

        $owner = $this->user_id === Auth::id() ? Auth::user() : $this->user;
        $warningSeconds = self::slaHoursFor($this->priority, $owner) * 3600 * self::SLA_WARNING_RATIO;

        return now()->diffInSeconds($this->due_at) <= $warningSeconds ? 'warning' : 'ok';
    }

    /** Kapanmış işlerde hedefe uyuldu mu? */
    public function metSla(): ?bool
    {
        if (! $this->closed_at || ! $this->due_at) {
            return null;
        }

        return $this->closed_at->lessThanOrEqualTo($this->due_at);
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereIn('status', TicketStatus::openValues());
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->open()->whereNotNull('due_at')->where('due_at', '<', now());
    }

    public function scopeAtRisk(Builder $query): Builder
    {
        // slaState() ile aynı kural: kalan süre, önceliğin SLA süresinin %25'inin altında
        return $query->open()->where(function (Builder $q) {
            foreach (self::slaTable(Auth::user()) as $priority => $hours) {
                $q->orWhere(fn (Builder $p) => $p->where('priority', $priority)->whereBetween('due_at', [
                    now(),
                    now()->addSeconds((int) ($hours * 3600 * self::SLA_WARNING_RATIO)),
                ]));
            }
        });
    }
}
