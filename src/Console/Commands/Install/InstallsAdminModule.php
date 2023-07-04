<?php

declare(strict_types=1);

namespace Thuraaung\Startup\Console\Commands\Install;

use Illuminate\Console\Command;

use function Safe\file_get_contents;

trait InstallsAdminModule
{
    /**
     * Install the Admin module.
     */
    protected function installAdminModule(): int|null
    {
        $with = $this->option('email') ? 'with Email Register ...' : "...";
        $this->components->info("Installing Admin Module " . $with);

        $folder = $this->option('email') ? 'admin-with-email-register' : 'admin';

        $this->setupAdminRoutes($folder);

        $this->components->info('Admin module scaffolding installed successfully.');

        return Command::SUCCESS;
    }

    private function setupAdminRoutes(string $folder): void
    {
        $this->appendToFile(
            path: base_path('routes/api/routes.php'),
            data: PHP_EOL . file_get_contents(__DIR__ . '/../../../../stubs/admin/routes/api/routes.stub')
        );

        $this->copyDirectory(
            source: __DIR__ . '/../../../../stubs/' . $folder . '/routes/api/admin',
            target: base_path('routes/api/admin')
        );
    }
}
