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
            'activeTickets' => Ticket::whereIn('status', ['open', 'in_progress'])->count(),
            'myActiveTickets' => Ticket::where('user_id', $userId)->whereIn('status', ['open', 'in_progress'])->count(),
            'openTasks' => Task::whereIn('status', ['open', 'in_progress'])->count(),
            'myOpenTasks' => Task::where('user_id', $userId)->whereIn('status', ['open', 'in_progress'])->count(),
        ])->layout('layouts.app');
    }
}
