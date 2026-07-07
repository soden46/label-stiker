<?php

namespace App\Providers;

use App\Services\LabelBrandingService;
use Carbon\Carbon;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        Carbon::setLocale('id');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('*', function ($view) {
            $branding = app(LabelBrandingService::class);
            $view->with([
                'brandName' => $branding->appName(),
                'brandLogoDataUri' => $branding->logoDataUri(),
                'brandFaviconDataUri' => $branding->faviconDataUri(),
            ]);
        });
    }
}
