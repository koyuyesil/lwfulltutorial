<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::view('dash', 'dash')
    ->middleware(['auth', 'verified'])
    ->name('dash');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::view('dvider', 'static.voltage-dvider')
    ->name('static.voltage-dvider');

// Route::view('tsk', 'livewire.tasks.tasks-index')
//     ->middleware(['auth'])
//     ->name('tasks.index');

require __DIR__.'/auth.php';
