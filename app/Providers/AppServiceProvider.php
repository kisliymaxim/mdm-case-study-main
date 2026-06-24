<?php

declare(strict_types=1);

namespace App\Providers;

use App\Mdm\MdmProviderRegistry;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function register(): void
    {
        $this->app->singleton(MdmProviderRegistry::class, function (Container $app) {
            return new MdmProviderRegistry(
                container: $app,
                providers: (array)config('mdm.providers', []),
                default: (string)config('mdm.default', 'jamf'),
            );
        });
    }

    /**
     * @return void
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);
    }
}
