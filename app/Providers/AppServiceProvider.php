<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\OnnxModelManager;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(OnnxModelManager::class, function () {
    return new OnnxModelManager();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
