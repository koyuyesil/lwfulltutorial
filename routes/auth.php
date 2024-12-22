<?php

use App\Http\Controllers\Auth\VerifyEmailController;
use App\Livewire\Customers\CustomersIndex;
use App\Livewire\Devices\DevicesIndex;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;
use App\Livewire\Tasks\TasksIndex;
use App\Livewire\Tasks\Show;

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

    Route::get('tasks', TasksIndex::class)->name('tasks.index');
    Route::get('devices', DevicesIndex::class)->name('devices.index');
    Route::get('customers', CustomersIndex::class)->name('customers.index');


});
