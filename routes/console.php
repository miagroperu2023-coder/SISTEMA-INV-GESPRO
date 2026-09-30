<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('app:enviar-comprobantes-pendientes')->everyFiveMinutes()->withoutOverlapping();

// procesa importaciones de Excel en segundo plano
Schedule::command('queue:work --stop-when-empty --tries=1 --timeout=120')->everyMinute()->withoutOverlapping();
