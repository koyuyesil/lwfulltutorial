<?php

namespace App\Livewire\Tasks;

use App\Enums\Recurrence;
use App\Enums\StatusType;
use App\Enums\TaskCategory;
use App\Models\Task;
use App\Support\QuickTaskParser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;

class TasksList extends Component
{
    #[Url(as: 'ara', except: '')]
    public string $search = '';

    #[Url(as: 'tur', except: '')]
    public string $category = '';

    /** '' | recurring | money */
    #[Url(as: 'filtre', except: '')]
    public string $filter = '';

    /** list | calendar */
    #[Url(as: 'gorunum', except: 'list')]
    public string $view = 'list';

    /** Takvimde gösterilen ay (Y-m) */
    #[Url(as: 'ay', except: '')]
    public string $month = '';

    public bool $showDone = false;

    public string $quick = '';

    public function placeholder()
    {
        return view('skeleton');
    }

    protected function find(int $id): Task
    {
        return Auth::user()->tasks()->findOrFail($id);
    }

    /* ------------------------------------------------------------ İşlemler */

    public function quickAdd(): void
    {
        $text = trim($this->quick);
        if (mb_strlen($text) < 2) {
            return;
        }

        $parsed = QuickTaskParser::parse($text);
        $user = Auth::user();

        // Saat yazılmadıysa firmanın varsayılan görev saati kullanılır
        if ($parsed['due_at'] && ! $parsed['has_time']) {
            [$h, $m] = array_map('intval', explode(':', (string) $user->setting('tasks.default_time')));
            $candidate = $parsed['due_at']->copy()->setTime($h, $m);
            $parsed['due_at'] = $candidate->isPast() && $candidate->isToday() ? $parsed['due_at'] : $candidate;
        }
        $remind = (string) $user->setting('tasks.default_remind');
        $task = $user->tasks()->create([
            'title' => $parsed['title'],
            'due_at' => $parsed['due_at'],
            'recurrence' => $parsed['recurrence'],
            'amount' => $parsed['amount'],
            'category' => $parsed['category'],
            'priority' => 'normal',
            'remind_before' => $parsed['due_at'] && $remind !== '' ? (int) $remind : null,
        ]);

        $this->quick = '';
        $when = $task->due_at ? $task->due_at->locale('tr')->translatedFormat('d F, H:i') : 'tarihsiz';
        $extra = $task->isRecurring() ? ' · '.$task->recurrence->describe() : '';
        $this->dispatch('toast', message: "Eklendi: {$task->title} ({$when}{$extra})");
        $this->dispatch('tasks-changed');
    }

    public function complete(int $id): void
    {
        $task = $this->find($id);
        $next = $task->complete();

        $message = $next
            ? "Tamamlandı. Sıradaki: {$next->due_at->locale('tr')->translatedFormat('d F Y')}"
            : 'Görev tamamlandı.';
        $this->dispatch('toast', message: $message);
        $this->dispatch('tasks-changed');
    }

    public function reopen(int $id): void
    {
        $this->find($id)->reopen();
        $this->dispatch('tasks-changed');
    }

    /** Eski API ile uyum: durum değiştirme. */
    public function changeStatus(int $id, string $status): void
    {
        $task = $this->find($id);
        $status = StatusType::from($status);

        match (true) {
            $status === StatusType::DONE && ! $task->isDone() => $task->complete(),
            $status !== StatusType::DONE && $task->isDone() => $task->reopen(),
            default => $task->update(['status' => $status]),
        };
        $this->dispatch('tasks-changed');
    }

    public function snooze(int $id, string $preset): void
    {
        $task = $this->find($id);
        $until = match ($preset) {
            '1h' => now()->addHour(),
            '3h' => now()->addHours(3),
            'tomorrow' => now()->addDay()->setTime(9, 0),
            'nextweek' => now()->next(Carbon::MONDAY)->setTime(9, 0),
            default => null,
        };
        if (! $until) {
            return;
        }

        // Tarihi geçmiş görevin tarihi de ertelenir; hatırlatma yeni zamana kurulur
        $attributes = ['remind_at' => $until];
        if (! $task->due_at || $task->due_at->lt($until)) {
            $attributes['due_at'] = $until;
        }
        $task->forceFill($attributes)->save();

        $this->dispatch('toast', message: 'Ertelendi: '.$until->locale('tr')->translatedFormat('d F, H:i'), type: 'info');
        $this->dispatch('tasks-changed');
    }

    /** Takvimde sürükle-bırak ile tarih değiştirme (saat korunur). */
    public function reschedule(int $id, string $date): void
    {
        $task = $this->find($id);
        $day = Carbon::parse($date);
        $time = $task->due_at ?? now()->setTime(9, 0);
        $task->update(['due_at' => $day->setTime($time->hour, $time->minute)]);

        $this->dispatch('toast', message: "Taşındı: {$task->title} → ".$day->locale('tr')->translatedFormat('d F'), type: 'info');
        $this->dispatch('tasks-changed');
    }

    public function delete(int $id, bool $series = false): void
    {
        $task = $this->find($id);

        if ($series && $task->isRecurring()) {
            Auth::user()->tasks()->where('series_id', $task->series_id)->open()->delete();
            $this->dispatch('toast', message: 'Tekrarlayan görev serisi silindi.');
        } else {
            $task->delete();
            $this->dispatch('toast', message: 'Görev silindi.');
        }
        $this->dispatch('tasks-changed');
    }

    public function clearFilters(): void
    {
        $this->reset('search', 'category', 'filter');
    }

    public function shiftMonth(int $delta): void
    {
        $this->month = $this->monthStart()->addMonthsNoOverflow($delta)->format('Y-m');
    }

    /* ------------------------------------------------------------ Sorgular */

    protected function monthStart(): Carbon
    {
        try {
            return $this->month ? Carbon::createFromFormat('Y-m', $this->month)->startOfMonth() : now()->startOfMonth();
        } catch (\Throwable) {
            return now()->startOfMonth();
        }
    }

    protected function filtered(): Builder
    {
        $term = trim($this->search);

        return Auth::user()->tasks()->getQuery()
            ->with('client:id,first_name,last_name')
            ->when($term !== '', fn ($q) => $q->where(fn ($q) => $q->where('title', 'like', "%$term%")->orWhere('description', 'like', "%$term%")))
            ->when(TaskCategory::tryFrom($this->category), fn ($q, $c) => $q->where('category', $c))
            ->when($this->filter === 'recurring', fn ($q) => $q->where('recurrence', '!=', Recurrence::NONE->value))
            ->when($this->filter === 'money', fn ($q) => $q->whereNotNull('amount'));
    }

    /** Gündem: gecikmiş / bugün / yarın / bu hafta / sonra / tarihsiz. */
    protected function agenda(): array
    {
        $open = $this->filtered()->open()
            ->orderByRaw('CASE WHEN due_at IS NULL THEN 1 ELSE 0 END')
            ->orderBy('due_at')
            ->limit(300)->get();

        $groups = ['overdue' => [], 'today' => [], 'tomorrow' => [], 'week' => [], 'later' => [], 'none' => []];
        foreach ($open as $task) {
            $groups[$task->bucket()][] = $task;
        }

        return array_filter($groups);
    }

    /** Takvim: ayın görevleri + tekrarlayan serilerin gelecekteki tahmini tarihleri. */
    protected function calendar(): array
    {
        $start = $this->monthStart();
        $end = $start->copy()->endOfMonth();
        $gridStart = $start->copy()->startOfWeek(Carbon::MONDAY);
        $gridEnd = $end->copy()->endOfWeek(Carbon::SUNDAY);

        $tasks = $this->filtered()->whereBetween('due_at', [$gridStart, $gridEnd])->orderBy('due_at')->get();
        $byDay = $tasks->groupBy(fn (Task $t) => $t->due_at->toDateString())->map->all()->all();

        // Her serinin en ileri açık görevinden sonrası tahmin edilir
        $heads = $this->filtered()->open()->where('recurrence', '!=', Recurrence::NONE->value)
            ->whereNotNull('due_at')->where('due_at', '<=', $gridEnd)
            ->orderByDesc('due_at')->get()->unique('series_id');

        $projected = [];
        foreach ($heads as $head) {
            $cursor = $head;
            for ($i = 0; $i < 62; $i++) {
                $nextDue = $cursor->nextDueAt();
                if (! $nextDue || $nextDue->gt($gridEnd)) {
                    break;
                }
                $ghost = $head->replicate();
                $ghost->due_at = $nextDue;
                if ($nextDue->gte($gridStart)) {
                    $projected[$nextDue->toDateString()][] = $ghost;
                }
                $cursor = $ghost;
                $cursor->series_id = $head->series_id;
            }
        }

        return [
            'start' => $start,
            'gridStart' => $gridStart,
            'gridEnd' => $gridEnd,
            'byDay' => $byDay,
            'projected' => $projected,
        ];
    }

    protected function stats(): array
    {
        $base = fn () => Auth::user()->tasks()->getQuery();
        $horizon = now()->addDays(30)->endOfDay();
        $money = $base()->open()->whereNotNull('amount')->where('due_at', '<=', $horizon)->get(['category', 'amount']);

        return [
            'overdue' => $base()->overdue()->count(),
            'today' => $base()->dueToday()->count(),
            'week' => $base()->open()->whereBetween('due_at', [now()->startOfDay(), now()->endOfWeek()])->count(),
            'income' => $money->filter(fn ($t) => $t->category->isIncome())->sum('amount'),
            'expense' => $money->reject(fn ($t) => $t->category->isIncome())->sum('amount'),
            'done7' => $base()->where('status', StatusType::DONE->value)->where('completed_at', '>=', now()->subDays(7))->count(),
        ];
    }

    #[On('task-saved')]
    #[On('tasks-changed')]
    public function render()
    {
        $categoryCounts = Auth::user()->tasks()->open()
            ->selectRaw('category, COUNT(*) as c')->groupBy('category')->pluck('c', 'category');

        return view('livewire.tasks.tasks-list', [
            'stats' => $this->stats(),
            'categoryCounts' => $categoryCounts,
            'agenda' => $this->view === 'list' ? $this->agenda() : [],
            'done' => $this->view === 'list' && $this->showDone
                ? $this->filtered()->where('status', StatusType::DONE->value)->latest('completed_at')->limit(30)->get()
                : collect(),
            'calendar' => $this->view === 'calendar' ? $this->calendar() : null,
        ]);
    }
}
