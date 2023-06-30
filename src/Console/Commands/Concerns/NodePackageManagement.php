<?php

declare(strict_types=1);

namespace Thuraaung\Startup\Console\Commands\Concerns;

use Illuminate\Filesystem\Filesystem;

use function file_exists;
use function json_decode;
use function file_get_contents;
use function array_key_exists;
use function ksort;
use function file_put_contents;
use function json_encode;
use function base_path;

trait NodePackageManagement
{
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
}
