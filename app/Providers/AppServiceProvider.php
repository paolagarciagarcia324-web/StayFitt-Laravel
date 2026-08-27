<?php

namespace App\Providers;

use App\View\Composers\AdminHeaderComposer;
use App\View\Composers\ClienteNavComposer;
use App\View\Composers\CoachNavComposer;
use App\View\Composers\StaffNavComposer;
use Illuminate\Pagination\Paginator;
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
        View::composer('layouts.admin', AdminHeaderComposer::class);
        View::composer('layouts.cliente', ClienteNavComposer::class);
        View::composer('layouts.coach', CoachNavComposer::class);
        View::composer('layouts.staff', StaffNavComposer::class);
        View::composer('layouts.institucional', \App\View\Composers\InstitucionalNavComposer::class);
        Paginator::defaultView('vendor.pagination.admin');
        Paginator::defaultSimpleView('vendor.pagination.admin');
    }
}
