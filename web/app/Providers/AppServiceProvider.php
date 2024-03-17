<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Observers\UserObserver;
use App\View\Components\Logo;
use App\View\Components\APPLogo;
use App\View\Components\WelcomeBody;
use App\View\Components\WelcomeFooter;
use Illuminate\Support\Facades\Blade;
use App\Models\User;

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
        User::observe(UserObserver::class);
        Blade::component('Logo', Logo::class);
        Blade::component('APP-Logo', APPLogo::class);
        Blade::component('WelcomeBody', WelcomeBody::class);
        Blade::component('WelcomeFooter', WelcomeFooter::class);

        if($this->app->environment('production')) {
            \URL::forceScheme('https');
        }
    }
}
