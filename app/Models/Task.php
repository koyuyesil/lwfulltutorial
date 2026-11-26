<?php

namespace App\Models;

use App\Enums\PriorityType;
use App\Enums\Recurrence;
use App\Enums\StatusType;
use App\Enums\TaskCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Task extends Model
{
    use \App\Models\Concerns\Trashable;
    use HasFactory;

    protected $fillable = [
        'title', 'slug', 'description', 'status', 'priority', 'deadline',
        'category', 'due_at', 'remind_before', 'remind_at',
        'recurrence', 'recurrence_interval', 'recurrence_until', 'series_id',
        'amount', 'client_id', 'ticket_id', 'completed_at',
    ];

    protected $casts = [
        'deadline' => 'date',
        'status' => StatusType::class,
        'priority' => PriorityType::class,
        'category' => TaskCategory::class,
        'recurrence' => Recurrence::class,
        'due_at' => 'datetime',
        'remind_at' => 'datetime',
        'recurrence_until' => 'date',
        'completed_at' => 'datetime',
        'amount' => 'decimal:2',
        'remind_before' => 'integer',
        'recurrence_interval' => 'integer',
    ];

    /** Hatırlatma seçenekleri (dakika). */
    public const REMIND_OPTIONS = [
        '' => 'Hatırlatma yok',
        '0' => 'Zamanında',
        '15' => '15 dakika önce',
        '60' => '1 saat önce',
        '1440' => '1 gün önce',
        '4320' => '3 gün önce',
        '10080' => '1 hafta önce',
    ];

    protected static function booted(): void
    {
        static::saving(function (Task $task) {
            $task->slug = Str::slug((string) $task->title) ?: null;
            $task->series_id ??= (string) Str::uuid();
            $task->status ??= StatusType::OPEN;
            $task->category ??= TaskCategory::GENERAL;
            $task->recurrence ??= Recurrence::NONE;
            $task->deadline = $task->due_at?->toDateString(); // eski sütunla uyum

            // Tarih ya da hatırlatma süresi değiştiyse hatırlatma zamanını yeniden hesapla
            if ($task->isDirty(['due_at', 'remind_before']) && ! $task->isDirty('remind_at')) {
                $task->remind_at = $task->due_at && $task->remind_before !== null
                    ? $task->due_at->copy()->subMinutes($task->remind_before)
                    : null;
            }
        });
    }

    public function setSlugAttribute($value)
    {
        $this->attributes['slug'] = $value === null ? null : Str::slug($value);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    /* ------------------------------------------------------------ Durum */

    public function isDone(): bool
    {
        return $this->status === StatusType::DONE;
    }

    public function isRecurring(): bool
    {
        return $this->recurrence !== null && $this->recurrence !== Recurrence::NONE;
    }

    public function isOverdue(): bool
    {
        return ! $this->isDone() && $this->due_at?->isPast();
    }

    /** overdue | today | tomorrow | week | later | none | done */
    public function bucket(): string
    {
        if ($this->isDone()) {
            return 'done';
        }
        if (! $this->due_at) {
            return 'none';
        }

        return match (true) {
            $this->due_at->isPast() && ! $this->due_at->isToday() => 'overdue',
            $this->due_at->isToday() => 'today',
            $this->due_at->isTomorrow() => 'tomorrow',
            $this->due_at->lte(now()->endOfWeek()) => 'week',
            default => 'later',
        };
    }

    /** Serideki bir sonraki tarih (tekrar bitmişse null). */
    public function nextDueAt(): ?Carbon
    {
        if (! $this->isRecurring() || ! $this->due_at) {
            return null;
        }

        $anchor = static::where('series_id', $this->series_id)->min('due_at');
        $anchorDay = $anchor ? Carbon::parse($anchor)->day : $this->due_at->day;

        $next = $this->recurrence->next($this->due_at, $this->recurrence_interval ?: 1, $anchorDay);

        if ($next && $this->recurrence_until && $next->startOfDay()->gt($this->recurrence_until)) {
            return null;
        }

        return $next?->setTimeFrom($this->due_at);
    }

    /**
     * Görevi tamamlar; tekrarlayan görevse serinin sonraki görevini oluşturur.
     */
    public function complete(): ?Task
    {
        return DB::transaction(function () {
            $this->forceFill(['status' => StatusType::DONE, 'completed_at' => now()])->save();

            $nextDue = $this->nextDueAt();
            if (! $nextDue) {
                return null;
            }

            // Aynı tarihli kopya zaten varsa (ör. geri al → tekrar tamamla) yenisini açma
            $existing = static::where('series_id', $this->series_id)
                ->where('due_at', $nextDue)->first();
            if ($existing) {
                return $existing;
            }

            $next = $this->replicate(['slug', 'completed_at', 'remind_at']);
            $next->forceFill([
                'status' => StatusType::OPEN,
                'due_at' => $nextDue,
                'completed_at' => null,
            ]);
            $next->user_id = $this->user_id;
            $next->save();

            return $next;
        });
    }

    /** Tamamlamayı geri alır; bu tamamlama ile açılmış ve dokunulmamış sonraki görevi siler. */
    public function reopen(): void
    {
        DB::transaction(function () {
            if ($nextDue = $this->nextDueAt()) {
                static::where('series_id', $this->series_id)
                    ->where('due_at', $nextDue)
                    ->where('status', StatusType::OPEN->value)
                    ->whereColumn('created_at', 'updated_at')
                    ->delete();
            }

            $this->forceFill(['status' => StatusType::OPEN, 'completed_at' => null])->save();
        });
    }

    public function snooze(Carbon $until): void
    {
        $this->forceFill(['remind_at' => $until])->save();
    }

    /* ------------------------------------------------------------ Kapsamlar */

    public function scopeOpen(Builder $q): Builder
    {
        return $q->where('status', '!=', StatusType::DONE->value);
    }

    public function scopeOverdue(Builder $q): Builder
    {
        return $q->open()->whereNotNull('due_at')->where('due_at', '<', now()->startOfDay());
    }

    public function scopeDueToday(Builder $q): Builder
    {
        return $q->open()->whereBetween('due_at', [now()->startOfDay(), now()->endOfDay()]);
    }

    /** Hatırlatma zamanı gelmiş açık görevler. */
    public function scopeReminderDue(Builder $q): Builder
    {
        return $q->open()->whereNotNull('remind_at')->where('remind_at', '<=', now());
    }

    /** Bildirim gerektirenler: gecikmiş, bugün ya da hatırlatma zamanı gelmiş. */
    public function scopeNeedsAttention(Builder $q): Builder
    {
        return $q->open()->where(fn (Builder $q) => $q
            ->where('due_at', '<=', now()->endOfDay())
            ->orWhere(fn ($q) => $q->whereNotNull('remind_at')->where('remind_at', '<=', now())));
    }

    public function formattedAmount(): ?string
    {
        return $this->amount !== null ? '₺'.number_format((float) $this->amount, 2, ',', '.') : null;
    }
}
