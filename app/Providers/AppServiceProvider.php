<?php

namespace App\Providers;

use App\Services\EngineStatusService;
use App\Support\ActiveYear;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Tahun terbit aktif + daftar tahun tersedia dipakai oleh dropdown
        // header pada seluruh halaman (layout bersama).
        View::composer('layouts.app', function ($view) {
            $view->with([
                'activeYear' => ActiveYear::get(),
                'availableYears' => ActiveYear::availableYears(),
                'engineStatus' => app(EngineStatusService::class)->status(),
            ]);
        });
    }
}
