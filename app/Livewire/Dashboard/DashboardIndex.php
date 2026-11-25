<?php

namespace App\Livewire\Dashboard;

use App\Models\Client;
use App\Models\ClientProduct;
use App\Models\Product;
use App\Models\Task;
use App\Models\Ticket;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Dashboard')]
class DashboardIndex extends Component
{
    public function render()
    {
        $userId = auth()->id();

        return view('livewire.dashboard.dashboard-index', [
            'stats' => [
                [
                    'label' => 'Müşteri',
                    'total' => Client::count(),
                    'mine' => Client::where('user_id', $userId)->count(),
                    'hint' => 'Toplam ve mevcut yetkilideki müşteri kayıtları',
                ],
                [
                    'label' => 'Kayıtlı cihaz',
                    'total' => ClientProduct::count(),
                    'mine' => ClientProduct::where('user_id', $userId)->count(),
                    'hint' => 'Servise alınmış müşteri cihazları',
                ],
                [
                    'label' => 'Ürün/model',
                    'total' => Product::count(),
                    'mine' => Product::where('user_id', $userId)->count(),
                    'hint' => 'Tanımlı cihaz marka ve model havuzu',
                ],
                [
                    'label' => 'Servis talebi',
                    'total' => Ticket::count(),
                    'mine' => Ticket::where('user_id', $userId)->count(),
                    'hint' => 'Açılan teknik servis kayıtları',
                ],
                [
                    'label' => 'Görev',
                    'total' => Task::count(),
                    'mine' => Task::where('user_id', $userId)->count(),
                    'hint' => 'Operasyon ve iş takibi',
                ],
            ],
            'activeTickets' => Ticket::open()->count(),
            'myActiveTickets' => Ticket::where('user_id', $userId)->open()->count(),
            'sla' => $this->slaMetrics($userId),
            'openTasks' => Task::whereIn('status', ['open', 'in_progress'])->count(),
            'myOpenTasks' => Task::where('user_id', $userId)->whereIn('status', ['open', 'in_progress'])->count(),
        ])->layout('layouts.app');
    }

    /** Oturumdaki yetkilinin servis hedefi (SLA) özeti. */
    protected function slaMetrics(int $userId): array
    {
        $mine = fn () => Ticket::where('user_id', $userId);

        $closed = $mine()->whereNotNull('closed_at')->whereNotNull('due_at')
            ->where('closed_at', '>=', now()->subDays(30))
            ->get(['closed_at', 'due_at', 'created_at']);

        $met = $closed->filter(fn (Ticket $t) => $t->metSla())->count();

        return [
            'overdue' => $mine()->overdue()->count(),
            'atRisk' => $mine()->atRisk()->count(),
            'closed30' => $closed->count(),
            'compliance' => $closed->isEmpty() ? null : (int) round($met / $closed->count() * 100),
            'avgHours' => $closed->isEmpty() ? null
                : round($closed->avg(fn (Ticket $t) => $t->created_at->diffInMinutes($t->closed_at)) / 60, 1),
            'pipeline' => $mine()->open()
                ->selectRaw('status, COUNT(*) as count')->groupBy('status')
                ->pluck('count', 'status'),
        ];
    }
}
