<?php

use Illuminate\Foundation\Inspiring;
use App\Services\KpiService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Chốt KPI các tháng đã kết thúc: chưa đạt -> 'failed' ("Không đạt")
Artisan::command('kpi:close-months', function (KpiService $kpiService) {
    $this->info('Đã chốt ' . $kpiService->closeEndedMonths() . ' KPI của các tháng đã kết thúc.');
})->purpose('Chốt trạng thái KPI (Hoàn thành / Không đạt) cho các tháng đã kết thúc');

Schedule::command('kpi:close-months')->dailyAt('00:10');
