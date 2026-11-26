<?php

use Livewire\Volt\Volt;
use App\Livewire\Tasks\TasksIndex;
use App\Livewire\Tools\ToolsIndex;
use Illuminate\Support\Facades\Route;
use App\Livewire\Clients\ClientsIndex;
use App\Livewire\Tickets\TicketsIndex;
use App\Livewire\Tickets\TicketsShow;
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
    Route::get('tickets/board', \App\Livewire\Tickets\TicketsBoard::class)->name('tickets.board');
    Route::get('tickets/{ticket}', TicketsShow::class)->name('tickets.show');
    Route::get('tickets/{ticket}/photos/{photo}', [\App\Http\Controllers\TicketDocumentsController::class, 'photo'])->name('tickets.photo');
    Route::get('tickets/{ticket}/print/{format}', [\App\Http\Controllers\TicketDocumentsController::class, 'print'])
        ->whereIn('format', \App\Http\Controllers\TicketDocumentsController::FORMATS)->name('tickets.print');
    Route::get('parts', \App\Livewire\Parts\PartsIndex::class)->name('parts.index');
    Route::get('tools', ToolsIndex::class)->name('tools.index');
    Route::get('tools/board-ids', BoardIdsIndex::class)->name('board-ids.index');
    Route::get('tools/cross-reference', \App\Livewire\CrossReference\CrossReferenceIndex::class)->name('cross-reference.index');
    Route::get('tools/cross-reference/export', [\App\Http\Controllers\CrossReferenceController::class, 'export'])->name('cross-reference.export');
    Route::get('tools/cross-reference/sample-csv', [\App\Http\Controllers\CrossReferenceController::class, 'sample'])->name('cross-reference.sample');

    // Ayarlar (modül bazlı sekmeler) ve cihaz kataloğu içe / dışa aktarma
    Route::get('settings/{tab?}', \App\Livewire\Settings\SettingsIndex::class)
        ->whereIn('tab', \App\Livewire\Settings\SettingsIndex::TABS)->name('settings');
    Route::get('clients/export', [\App\Http\Controllers\ClientContactsController::class, 'export'])->name('clients.export');
    Route::get('clients/{client}/vcard', [\App\Http\Controllers\ClientContactsController::class, 'vcard'])->name('clients.vcard');
    Route::get('tools/board-ids/export', [\App\Http\Controllers\BoardIdCatalogController::class, 'export'])->name('board-ids.export');
    Route::get('tools/board-ids/sample-csv', [\App\Http\Controllers\BoardIdCatalogController::class, 'sample'])->name('board-ids.sample');
    Route::get('products/export', [\App\Http\Controllers\DeviceCatalogController::class, 'export'])->name('products.export');
    Route::get('products/sample-csv', [\App\Http\Controllers\DeviceCatalogController::class, 'sample'])->name('products.sample');


});
