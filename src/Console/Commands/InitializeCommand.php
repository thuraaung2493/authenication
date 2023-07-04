<?php

declare(strict_types=1);

namespace Thuraaung\Startup\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Thuraaung\Startup\Console\Commands\Concerns\ComposerPackageManagement;
use Thuraaung\Startup\Console\Commands\Concerns\FileManagement;

final class InitializeCommand extends Command
{
    use ComposerPackageManagement;
    use FileManagement;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'init
                            {--S|larastan : Indicate that setup the Larastan configuration.}
                            {--P|laravel-pint : Indicate that setup the Larastan configuration.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Initialize the API project.";

    public function handle(): int|null
    {
        $this->components->info('Initializing ...');

        $this->removeScaffoldingUnnecessary();

        $this->setupApiRoutesStructure();

        $this->setupProviders();

        $this->components->info('Scaffolding completed.');

        if ($this->option('larastan') && Command::FAILURE === $this->call('setup:larastan')) {
            $this->components->error('The setup of Larastan failed!');
        }

        if ($this->option('laravel-pint') && Command::FAILURE === $this->call('setup:laravel-pint')) {
            $this->components->error('The setup of Larastan Pint failed!');
        }

        return Command::SUCCESS;
    }

    private function setupProviders(): void
    {
        $this->copyDirectory(
            source: __DIR__ . '/../../../stubs/init/app/Providers',
            target: app_path('Providers'),
        );
    }

    private function setupApiRoutesStructure(): void
    {
        $this->replaceInFile("require base_path('routes/console.php');", '', app_path('Console/Kernel.php'));
        $this->replaceInFile('$this->load(__DIR__ . \'/Commands\');' . PHP_EOL . PHP_EOL, '$this->load(__DIR__ . \'/Commands\');', app_path('Console/Kernel.php'));

        $this->copyDirectory(
            source: __DIR__ . '/../../../stubs/init/routes',
            target: base_path('routes'),
        );
    }


    private function removeScaffoldingUnnecessary(): void
    {
        $files = new Filesystem();

        // Remove providers directory...
        $files->deleteDirectory(app_path('Providers'));

        // Remove routes directory...
        $files->deleteDirectory(base_path('routes'));

        // Remove RedirectIfAuthenticated middleware...
        $files->delete(app_path('Http/Middleware/RedirectIfAuthenticated.php'));

        // Remove frontend related files...
        $files->delete(base_path('package.json'));
        $files->delete(base_path('vite.config.js'));

        // Remove Laravel "welcome" view...
        $files->delete(resource_path('views/welcome.blade.php'));
        $files->put(resource_path('views/.gitkeep'), PHP_EOL);

        // Remove CSS and JavaScript directories...
        $files->deleteDirectory(resource_path('css'));
        $files->deleteDirectory(resource_path('js'));

        // Remove Unit test directory...
        $files->deleteDirectory(base_path('tests/Unit'));
    }
}
