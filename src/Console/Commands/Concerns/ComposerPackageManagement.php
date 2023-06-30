<?php

declare(strict_types=1);

namespace Thuraaung\Startup\Console\Commands\Concerns;

use Illuminate\Support\Arr;
use Symfony\Component\Process\Process;

use function file_put_contents;
use function json_encode;
use function json_decode;
use function file_get_contents;
use function array_merge;

trait ComposerPackageManagement
{
    private function updateComposerFile(callable $callback): void
    {
        $packages = (array) json_decode(file_get_contents(\base_path('composer.json')), true);

        $callback($packages);

        file_put_contents(\base_path('composer.json'), json_encode($packages, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . PHP_EOL);
    }

    protected function checkExistsComposerPackages(array $packages, bool $asDev = false): bool
    {
        $composerPackages = (array) json_decode(file_get_contents(\base_path("composer.json")), true);

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

        return 0 === (new Process($command, \base_path(), ['COMPOSER_MEMORY_LIMIT' => '-1']))
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
}
