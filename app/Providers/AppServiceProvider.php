<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

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
        if ($this->app->runningInConsole()) {
            return;
        }

        $request = request();
        if (! $request || ! $request->getHost()) {
            return;
        }

        $basePath = rtrim((string) env('APP_BASE_PATH', ''), '/');
        $scheme = $request->isSecure() || $this->app->environment('production')
            ? 'https'
            : 'http';

        URL::forceScheme($scheme);
        URL::forceRootUrl($scheme.'://'.$request->getHost().$basePath);
    }
}
