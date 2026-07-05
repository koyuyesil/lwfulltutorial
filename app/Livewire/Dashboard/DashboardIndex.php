<?php

namespace App\Livewire\Dashboard;

use App\Models\Client;
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
        return view('livewire.dashboard.dashboard-index', [
            'stats' => [
                ['label' => 'Müşteri', 'value' => Client::count(), 'hint' => 'Kayıtlı servis müşterisi'],
                ['label' => 'Cihaz modeli', 'value' => Product::count(), 'hint' => 'Tanımlı ürün/model'],
                ['label' => 'Servis talebi', 'value' => Ticket::count(), 'hint' => 'Açılan teknik kayıt'],
                ['label' => 'Görev', 'value' => Task::count(), 'hint' => 'Operasyon takibi'],
            ],
            'activeTickets' => Ticket::whereIn('status', ['open', 'in_progress'])->count(),
            'openTasks' => Task::whereIn('status', ['open', 'in_progress'])->count(),
        ])->layout('layouts.app');
    }
}
