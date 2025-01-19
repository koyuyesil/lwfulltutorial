<?php

use Livewire\Volt\Volt;
use App\Livewire\Tasks\TasksIndex;
use App\Livewire\Tools\ToolsIndex;
use Illuminate\Support\Facades\Route;
use App\Livewire\Clients\ClientsIndex;
use App\Livewire\Tickets\TicketsIndex;
use App\Livewire\BoardIds\BoardIdsIndex;
use App\Livewire\Products\ProductsIndex;
use App\Livewire\Dashboard\DashboardIndex;
use App\Http\Controllers\Auth\VerifyEmailController;





Route::middleware('guest')->group(function () {
    Volt::route('register', 'pages.auth.register')
        ->name('register');

    Volt::route('login', 'pages.auth.login')
        ->name('login');

    Volt::route('forgot-password', 'pages.auth.forgot-password')
        ->name('password.request');

    Volt::route('reset-password/{token}', 'pages.auth.reset-password')
        ->name('password.reset');
});

Route::middleware('auth')->group(function () {
    Volt::route('verify-email', 'pages.auth.verify-email')
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Volt::route('confirm-password', 'pages.auth.confirm-password')
        ->name('password.confirm');

    Route::get('dashboard', DashboardIndex::class)->name('dashboard');
    Route::get('clients', ClientsIndex::class)->name('clients.index');
    Route::get('tasks', TasksIndex::class)->name('tasks.index');
    Route::get('products', ProductsIndex::class)->name('products.index');
    Route::get('tickets', TicketsIndex::class)->name('tickets.index');
    Route::get('tools', ToolsIndex::class)->name('tools.index');
    Route::get('board-ids', BoardIdsIndex::class)->name('board-ids.index');


});
