<?php

declare(strict_types=1);

namespace Thuraaung\Startup\Console\Commands\Concerns;

use Illuminate\Filesystem\Filesystem;

use function file_put_contents;
use function str_replace;
use function file_get_contents;
use function copy;

trait FileManagement
{
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
