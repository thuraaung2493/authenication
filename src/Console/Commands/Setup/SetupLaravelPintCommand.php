<?php

declare(strict_types=1);

namespace Thuraaung\Startup\Console\Commands\Setup;

use Illuminate\Console\Command;
use Thuraaung\Startup\Console\Commands\Concerns\ComposerPackageManagement;
use Thuraaung\Startup\Console\Commands\Concerns\FileManagement;

final class SetupLaravelPintCommand extends Command
{
    use ComposerPackageManagement;
    use FileManagement;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'setup:laravel-pint
                            {--composer=global : Absolute path to the Composer binary which should be used to install packages}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Setup the Laravel Pint configuration.";

    public function handle(): int|null
    {
        $this->components->info('Setting up the Laravel Pint configuration ...');

        $this->copyFile(
            source: __DIR__ . '/../../../../stubs/laravel-pint/pint.json',
            target: base_path('pint.json'),
        );

        if ( ! $this->checkExistsComposerPackages(['laravel/pint'], true)) {
            $this->requireComposerPackages(
                packages: ['laravel/pint'],
                asDev: true
            );
        }

        $this->updateComposerFile(function (&$packages): void {
            $packages['scripts']['pint'] = "./vendor/bin/pint";
        });

        $this->components->info('Laravel Pint configuration setup completed.');

        return Command::SUCCESS;
    }
}
