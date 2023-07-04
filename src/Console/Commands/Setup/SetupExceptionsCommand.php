<?php

declare(strict_types=1);

namespace Thuraaung\Startup\Console\Commands\Setup;

use Illuminate\Console\Command;
use Thuraaung\Startup\Console\Commands\Concerns\ComposerPackageManagement;
use Thuraaung\Startup\Console\Commands\Concerns\FileManagement;

final class SetupLarastanCommand extends Command
{
    use ComposerPackageManagement;
    use FileManagement;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'setup:exceptions
                            {--composer=global : Absolute path to the Composer binary which should be used to install packages}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Setup the exception handling.";

    public function handle(): int|null
    {
        $this->components->info('Setting up the exception handling ...');

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
