<?php

namespace App\Livewire;

use App\Models\Task;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Üst çubuktaki bildirim zili: ilgi bekleyen görevler (gecikmiş, bugün, hatırlatması gelmiş)
 * ve teslim hedefi geçmiş servis kayıtları. Dakikada bir kendini yeniler.
 */
class NotificationBell extends Component
{
    public function complete(int $id): void
    {
        $task = Auth::user()->tasks()->findOrFail($id);
        $next = $task->complete();

        $this->dispatch('toast', message: $next
            ? "Tamamlandı. Sıradaki: {$next->due_at->locale('tr')->translatedFormat('d F')}"
            : 'Görev tamamlandı.');
        $this->dispatch('tasks-changed');
    }

    public function snooze(int $id): void
    {
        $task = Auth::user()->tasks()->findOrFail($id);
        $until = now()->addHour();
        $attributes = ['remind_at' => $until];
        if (! $task->due_at || $task->due_at->lt($until)) {
            $attributes['due_at'] = $until;
        }
        $task->forceFill($attributes)->save();

        $this->dispatch('toast', message: '1 saat ertelendi.', type: 'info');
        $this->dispatch('tasks-changed');
    }

    #[On('tasks-changed')]
    #[On('task-saved')]
    public function render()
    {
        $user = Auth::user();

        $tasks = $user->tasks()->needsAttention()
            ->orderBy('due_at')->limit(8)->get();
        $taskCount = $user->tasks()->needsAttention()->count();

        $tickets = $user->tickets()->overdue()
            ->with('clientProduct.client:id,first_name,last_name', 'clientProduct.product:id,brand,model_name')
            ->orderBy('due_at')->limit(5)->get();
        $ticketCount = $user->tickets()->overdue()->count();

        return view('livewire.notification-bell', [
            'tasks' => $tasks,
            'tickets' => $tickets,
            'taskCount' => $taskCount,
            'ticketCount' => $ticketCount,
            'total' => $taskCount + $ticketCount,
        ]);
    }
}
