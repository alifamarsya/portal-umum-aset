<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Jadwal pemantauan batas SLA tiket aktif setiap 15 menit
Schedule::command('tickets:check-sla')->everyFifteenMinutes();
Schedule::job(new \App\Jobs\CheckTicketSlaJob)->everyFifteenMinutes();

// Jadwal ETL Data Warehouse otomatis setiap awal bulan jam 02:00
Schedule::command('dw:etl')->monthlyOn(1, '02:00');