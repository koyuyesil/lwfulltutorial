<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Çöp kutusunda saklama süresi (30 gün) dolan kayıtları kalıcı sil
Schedule::command('model:prune')->dailyAt('03:30')->withoutOverlapping();
