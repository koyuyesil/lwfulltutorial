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

    protected $fillable = ['user_id', 'client_product_id', 'problem', 'priority', 'status', 'due_at'];

    // Veritabanından gelen verileri tipine dönüştür
    protected $casts = [
        'status' => TicketStatus::class,
        'priority' => PriorityType::class,
        'due_at' => 'datetime',
        'status_changed_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Ticket $ticket) {
            $ticket->status ??= TicketStatus::RECEIVED;
            $ticket->status_changed_at ??= now();
            $ticket->due_at ??= now()->addHours(self::slaHoursFor($ticket->priority));
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

    public static function slaHoursFor(PriorityType|string|null $priority): int
    {
        $key = $priority instanceof PriorityType ? $priority->value : (string) $priority;

        return self::SLA_HOURS[$key] ?? self::SLA_HOURS['normal'];
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

        $warningSeconds = self::slaHoursFor($this->priority) * 3600 * self::SLA_WARNING_RATIO;

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
            foreach (self::SLA_HOURS as $priority => $hours) {
                $q->orWhere(fn (Builder $p) => $p->where('priority', $priority)->whereBetween('due_at', [
                    now(),
                    now()->addSeconds((int) ($hours * 3600 * self::SLA_WARNING_RATIO)),
                ]));
            }
        });
    }
}
