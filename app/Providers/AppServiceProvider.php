<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Vite;

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
        $basePath = $this->resolveAssetBasePath();

        if ($basePath !== '') {
            URL::useAssetOrigin($basePath);

            Vite::createAssetPathsUsing(function (string $path) use ($basePath): string {
                return $basePath.'/'.ltrim($path, '/');
            });
        }

        if ($this->app->runningInConsole()) {
            return;
        }

        $request = request();
        if (! $request || ! $request->getHost()) {
            return;
        }

        $scheme = $request->isSecure() ? 'https' : 'http';
        $host = $request->header('X-Forwarded-Host') ?? $request->getHost();
        $host = trim(explode(',', $host)[0]);

        URL::forceScheme($scheme);
        URL::forceRootUrl($scheme.'://'.$host.$basePath);
    }

    /**
     * Ruta base de assets sin host (evita CORS en módulos ES de Vite).
     */
    private function resolveAssetBasePath(): string
    {
        $assetUrl = rtrim((string) config('app.asset_url', env('APP_BASE_PATH', '')), '/');

        if ($assetUrl === '') {
            return '';
        }

        if (str_starts_with($assetUrl, 'http://') || str_starts_with($assetUrl, 'https://')) {
            return rtrim(parse_url($assetUrl, PHP_URL_PATH) ?: '', '/');
        }

        return $assetUrl;
    }
}
