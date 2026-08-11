<?php

namespace App\Providers;

use App\Support\MailSettings;
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
        // Let the admin-configured mail settings (settings table) override the
        // .env defaults without a deploy. No-op until the settings table exists.
        MailSettings::apply();
    }
}
