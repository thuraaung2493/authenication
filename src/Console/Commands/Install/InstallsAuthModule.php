<?php

declare(strict_types=1);

namespace Thuraaung\Startup\Console\Commands\Install;

use Illuminate\Console\Command;

trait InstallsAuthModule
{
    /**
     * Install the Auth module.
     */
    protected function installAuthModule(): int|null
    {
        $with = $this->option('email') ? 'with Email Register ...' : "...";
        $this->components->info("Installing Auth Module " . $with);

        $folder = $this->option('email') ? 'auth-with-email-register' : 'auth';

        $this->setupRoutes($folder);

        $this->setupControllers($folder);

        $this->setupRequests($folder);

        $this->setupDataObjects($folder);

        $this->setupEnums($folder);

        $this->setupExceptions($folder);

        $this->setupCqrs($folder);

        $this->setupModels($folder);

        if ($this->option('email')) {
            $this->setupRules($folder);

            $this->setupMails($folder);
        }

        $this->installPackages();

        // Tests...
        if ( ! $this->installAuthenticationTests($folder)) {
            return Command::FAILURE;
        }

        $this->components->info('Auth module scaffolding installed successfully.');

        return Command::SUCCESS;
    }

    private function installAuthenticationTests(string $folder): bool
    {
        if ( ! $this->option('pest')) {
            return false;
        }

        $this->removeComposerPackages(['phpunit/phpunit'], true);

        $requiredPackages = $this->requireComposerPackages([
            'pestphp/pest:^2.0',
            'pestphp/pest-plugin-laravel:^2.0',
            'nunomaduro/mock-final-classes'
        ], true);

        if ( ! $requiredPackages) {
            return false;
        }

        $this->copyDirectory(
            source: __DIR__ . '/../../../../stubs/' . $folder . '/tests/Feature',
            target: base_path('tests/Feature'),
        );

        $this->copyFile(
            source: __DIR__ . '/../../../../stubs/' . $folder . '/tests/Pest.php',
            target: base_path('tests/Pest.php'),
        );
        $this->copyFile(
            source: __DIR__ . '/../../../../stubs/' . $folder . '/tests/Helpers.php',
            target: base_path('tests/Helpers.php'),
        );

        $this->updateComposerFile(function (&$packages): void {
            $packages['scripts']['test'] = "./vendor/bin/pest --colors=always --parallel";
        });

        return true;
    }

    private function installPackages(): void
    {
        $this->updateComposerFile(function (&$packages): void {
            $packages['minimum-stability'] = 'dev';
        });

        $packages = [
            'thuraaung2493/laravel-make-files-tools:dev-main',
            'thuraaung2493/laravel-api-helpers:dev-main',
            'thuraaung2493/space-file-storage:dev-main',
        ];

        if ( ! $this->checkExistsComposerPackages(['laravel/sanctum'])) {
            array_push($packages, 'laravel/sanctum');
        }

        $this->requireComposerPackages($packages);

        if ($this->option('email')) {
            $this->requireComposerPackages(['thuraaung2493/otp-generator:dev-main']);
        }
    }

    private function setupRoutes(string $folder): void
    {
        $this->copyDirectory(
            source: __DIR__ . '/../../../../stubs/' . $folder . '/routes/api',
            target: base_path('routes/api')
        );
    }

    private function setupControllers(string $folder): void
    {
        $this->copyDirectory(
            source: __DIR__ . '/../../../../stubs/' . $folder . '/app/Http/Controllers/Auth',
            target: app_path('Http/Controllers/Auth')
        );
    }

    private function setupRequests(string $folder): void
    {
        $this->copyDirectory(
            source: __DIR__ . '/../../../../stubs/' . $folder . '/app/Http/Requests/Concerns',
            target: app_path('Http/Requests/Concerns'),
        );

        $this->copyDirectory(
            source: __DIR__ . '/../../../../stubs/' . $folder . '/app/Http/Requests/Auth',
            target: app_path('Http/Requests/Auth'),
        );
    }

    private function setupRules(string $folder): void
    {
        $this->copyFile(
            source: __DIR__ . '/../../../../stubs/' . $folder . '/app/Rules/CheckEmailLoginUnique.php',
            target: app_path('Rules/CheckEmailLoginUnique'),
        );
    }

    private function setupDataObjects(string $folder): void
    {
        $this->copyDirectory(
            source: __DIR__ . '/../../../../stubs/' . $folder . '/app/DataObjects/Auth',
            target: app_path('DataObjects/Auth'),
        );
    }

    private function setupEnums(string $folder): void
    {
        $this->copyFile(
            source: __DIR__ . '/../../../../stubs/' . $folder . '/app/Enums/LoginType.php',
            target: app_path('Enums/LoginType.php'),
        );
    }

    private function setupExceptions(string $folder): void
    {
        $this->copyDirectory(
            source: __DIR__ . '/../../../../stubs/' . $folder . '/app/Exceptions/Concerns',
            target: app_path('Exceptions/Concerns'),
        );

        if ($this->option('email')) {
            $this->copyDirectory(
                source: __DIR__ . '/../../../../stubs/' . $folder . '/app/Exceptions/Auth',
                target: app_path('Exceptions/Auth'),
            );
        }
    }

    private function setupCqrs(string $folder): void
    {
        // Commands
        $this->copyDirectory(
            source: __DIR__ . '/../../../../stubs/' . $folder . '/app/Commands',
            target: app_path('Commands'),
        );

        // Queries
        $this->copyDirectory(
            source: __DIR__ . '/../../../../stubs/' . $folder . '/app/Queries',
            target: app_path('Queries'),
        );
    }

    private function setupModels(string $folder): void
    {
        $this->copyFile(
            source: __DIR__ . '/../../../../stubs/' . $folder . '/databases/migrations/0000_00_00_000000_create_users_table.php',
            target: database_path('factories/' . now()->format('Y_m_d_His') . '_create_users_table.php'),
        );

        $this->copyFile(
            source: __DIR__ . '/../../../../stubs/' . $folder . '/databases/factories/UserFactory.php',
            target: database_path('factories/UserFactory.php'),
        );

        $this->copyDirectory(
            source: __DIR__ . '/../../../../stubs/' . $folder . '/app/Models',
            target: app_path('Models'),
        );

        if ($this->option('email')) {
            $this->copyFile(
                source: __DIR__ . '/../../../../stubs/' . $folder . '/databases/migrations/0000_00_00_000000_create_otps_table.php',
                target: database_path('factories/' . now()->format('Y_m_d_His') . '_create_otps_table.php'),
            );

            $this->copyFile(
                source: __DIR__ . '/../../../../stubs/' . $folder . '/databases/factories/OtpFactory.php',
                target: database_path('factories/OtpFactory.php'),
            );
        }
    }

    private function setupMails(string $folder): void
    {
        $this->copyFile(
            source: __DIR__ . '/../../../../stubs/' . $folder . '/app/Mail/SendOtpCode.php',
            target: app_path('Mail/SendOtpCode.php'),
        );

        $this->copyFile(
            source: __DIR__ . '/../../../../stubs/' . $folder . '/resources/views/emails/send-otp.blade.php',
            target: resource_path('views/emails/send-otp.blade.php'),
        );
    }
}
