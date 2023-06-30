<?php

declare(strict_types=1);

namespace Thuraaung\Startup\Console\Commands\Install;

use Illuminate\Console\Command;

trait InstallsExceptionModule
{
    /**
     * Install the Exception module.
     */
    protected function installExceptionModule(): int|null
    {
        $this->components->info('Installing Exception Module ...');

        $this->copyFile(
            source: __DIR__ . '/../../stubs/exceptions/lang/en/exceptions.php',
            target: base_path('lang/en/exceptions.php'),
        );

        $this->copyFile(
            source: __DIR__ . '/../../stubs/exceptions/app/Exceptions/Handler.php',
            target: app_path('Exceptions/Handler.php'),
        );

        if ( ! $this->checkExistsComposerPackages(["thuraaung2493/laravel-api-helpers"])) {
            $this->requireComposerPackages(['thuraaung2493/laravel-api-helpers:dev-main']);
        }

        $this->components->info('Exception module scaffolding installed successfully.');

        return Command::SUCCESS;
    }
}
