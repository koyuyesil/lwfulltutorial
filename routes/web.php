<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::view('servisler', 'static.services.index')->name('services.index');
Route::view('servisler/ariza-teshis-kabul', 'static.services.diagnostics')->name('services.diagnostics');
Route::view('servisler/ekran-kasa-islemleri', 'static.services.display')->name('services.display');
Route::view('servisler/guc-sarj-sorunlari', 'static.services.power')->name('services.power');
Route::view('servisler/anakart-mikro-lehim', 'static.services.board')->name('services.board');
Route::view('servisler/yazilim-veri-destegi', 'static.services.software')->name('services.software');
Route::view('servisler/kurumsal-servis-takibi', 'static.services.tracking')->name('services.tracking');

Route::view('dash', 'dash')
    ->middleware(['auth', 'verified'])
    ->name('dash');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::view('test', 'static.test')
    ->name('test');

// Route::view('tsk', 'livewire.tasks.tasks-index')
//     ->middleware(['auth'])
//     ->name('tasks.index');

require __DIR__.'/auth.php';
