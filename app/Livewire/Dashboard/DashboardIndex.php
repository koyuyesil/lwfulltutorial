<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use Livewire\Attributes\Title;

#[Title('Dashboard')]
class DashboardIndex extends Component
{
    public function render()
    {
        return view('livewire.dashboard.dashboard-index')->layout('layouts.app');
    }
}
