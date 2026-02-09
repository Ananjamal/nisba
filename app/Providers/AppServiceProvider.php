<?php

namespace App\Providers;

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
        \App\Models\Lead::observe(\App\Observers\LeadObserver::class);

        // Load dynamic settings into config
        if (\Schema::hasTable('settings')) {
            $settings = \App\Models\Setting::all();
            foreach ($settings as $setting) {
                config(['app.' . $setting->key => $setting->value]);
                // Also update app name specifically
                if ($setting->key === 'site_name') {
                    config(['app.name' => $setting->value]);
                }
            }
        }
    }
}
