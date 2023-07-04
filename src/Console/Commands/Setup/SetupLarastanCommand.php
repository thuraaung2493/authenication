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
    protected $signature = 'setup:larastan
                            {--composer=global : Absolute path to the Composer binary which should be used to install packages}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Setup the Larastan configuration.";

    public function handle(): int|null
    {
        $this->components->info('Setting up the Larastan configuration ...');

        return Command::FAILURE;
        $this->copyFile(
            source: __DIR__ . '/../../../../stubs/larastan/phpstan.neon',
            target: base_path('phpstan.neon'),
        );

        if ( ! $this->checkExistsComposerPackages(['nunomaduro/larastan:^2.0'], true)) {
            $this->requireComposerPackages(
                packages: ['nunomaduro/larastan:^2.0'],
                asDev: true
            );
        }

        $this->updateComposerFile(function (&$packages): void {
            $packages['scripts']['analyse'] = "./vendor/bin/phpstan analyse --memory-limit=256m";
        });

        $this->components->info('Larastan configuration setup completed.');

        return Command::SUCCESS;
    }
}
