<?php

declare(strict_types=1);

namespace Thuraaung\Startup\Providers;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use Thuraaung\Startup\Console\InstallCommand;

final class StartupServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public function boot(): void
    {
        if ( ! $this->app->runningInConsole()) {
            return;
        }

        $this->commands([
            InstallCommand::class,
        ]);
    }

    public function provides()
    {
        return [
            InstallCommand::class,
        ];
    }
}
