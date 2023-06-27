<?php

declare(strict_types=1);

namespace Thuraaung\Startup\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

use function Safe\file_get_contents;

final class InstallCommand extends Command
{
    use InstallsAuthModule;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'startup:install {module : The module that should be installed (auth)}
                            {--email : Indicate that Email Register should be installed}
                            {--pest : Indicate that Pest should be installed}
                            {--composer=global : Absolute path to the Composer binary which should be used to install packages}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Startup a laravel project";

    protected array $modules = ['auth'];

    public function handle(): int|null
    {
        if ('auth' === $this->argument('module')) {
            $this->components->info('Installing Auth Module ...');
            return $this->installAuthModule();
        }

        $this->components->error('Invalid module. Supported modules are [auth].');

        return 1;
    }

    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        if (null === $this->argument('module')) {
            $input->setArgument('module', $this->components->choice('Which module would you like to install?', $this->modules));

            $input->setOption('pest', $this->components->confirm('Would you prefer to install Pest?'));
        }

        if ($this->argument('module')) {
            return;
        }
    }

    protected function checkExistsComposerPackages(array $packages, bool $asDev = false): bool
    {
        $composerPackages = (array) json_decode(file_get_contents(base_path("composer.json")), true);

        $configurationKey = $asDev ? 'require-dev' : 'require';

        return Arr::has((array) $composerPackages[$configurationKey], $packages);
    }

    /**
     * Installs the given Composer Packages into the application.
     */
    protected function requireComposerPackages(array $packages, bool $asDev = false): bool
    {
        $composer = $this->option('composer');

        if ('global' !== $composer) {
            $command = ['php', $composer, 'require'];
        }

        $command = array_merge(
            $command ?? ['composer', 'require'],
            $packages,
            $asDev ? ['--dev'] : [],
        );

        return 0 === (new Process($command, base_path(), ['COMPOSER_MEMORY_LIMIT' => '-1']))
            ->setTimeout(null)
            ->run(function ($type, $output): void {
                $this->output->write($output);
            });
    }

    /**
     * Removes the given Composer Packages from the application.
     */
    protected function removeComposerPackages(array $packages, bool $asDev = false): bool
    {
        $composer = $this->option('composer');

        if ('global' !== $composer) {
            $command = ['php', $composer, 'remove'];
        }

        $command = array_merge(
            $command ?? ['composer', 'remove'],
            $packages,
            $asDev ? ['--dev'] : [],
        );

        return 0 === (new Process($command, base_path(), ['COMPOSER_MEMORY_LIMIT' => '-1']))
            ->setTimeout(null)
            ->run(function ($type, $output): void {
                $this->output->write($output);
            });
    }

    /**
     * Update the "package.json" file.
     */
    protected static function updateNodePackages(callable $callback, bool $dev = true): void
    {
        if ( ! file_exists(base_path('package.json'))) {
            return;
        }

        $configurationKey = $dev ? 'devDependencies' : 'dependencies';

        $packages = (array) json_decode(file_get_contents(base_path('package.json')), true);

        $packages[$configurationKey] = $callback(
            array_key_exists($configurationKey, $packages) ? $packages[$configurationKey] : [],
            $configurationKey
        );

        ksort($packages[$configurationKey]);

        file_put_contents(
            base_path('package.json'),
            json_encode($packages, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . PHP_EOL
        );
    }

    /**
     * Delete the "node_modules" directory and remove the associated lock files.
     */
    protected static function flushNodeModules(): void
    {
        tap(new Filesystem(), function ($files): void {
            $files->deleteDirectory(base_path('node_modules'));

            $files->delete(base_path('yarn.lock'));
            $files->delete(base_path('package-lock.json'));
        });
    }

    /**
     * Replace a given string within a given file.
     */
    protected function replaceInFile(string $search, string $replace, string $path): void
    {
        file_put_contents($path, str_replace($search, $replace, file_get_contents($path)));
    }

    /**
     * Copy a directory.
     */
    protected function copyDirectory(string $source, string $target): void
    {
        $files = new Filesystem();
        $files->ensureDirectoryExists($target);
        $files->copyDirectory($source, $target);
    }

    /**
     * Copy a file.
     */
    protected function copyFile(string $source, string $target): void
    {
        $files = new Filesystem();
        $files->ensureDirectoryExists($files->dirname($target));
        copy($source, $target);
    }
}
