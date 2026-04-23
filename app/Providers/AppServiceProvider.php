<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Usaha;
use App\Models\Tenant;
use App\Models\Menu;
use App\Policies\UsahaPolicy;
use App\Policies\TenantPolicy;
use App\Policies\MenuPolicy;

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
    Gate::policy(Usaha::class,  UsahaPolicy::class);
    Gate::policy(Tenant::class, TenantPolicy::class);
    Gate::policy(Menu::class,   MenuPolicy::class);
    }
}
