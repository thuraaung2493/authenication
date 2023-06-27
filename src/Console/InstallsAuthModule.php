<?php

declare(strict_types=1);

namespace Thuraaung\Startup\Console;

use Illuminate\Filesystem\Filesystem;

trait InstallsAuthModule
{
    /**
     * Install the Auth module.
     */
    protected function installAuthModule(): int|null
    {
        $files = new Filesystem();

        $folder = $this->option('email') ? 'auth-with-email-register' : 'auth';

        $this->setupLangFile($folder);

        $this->setupRoutes($folder);

        $this->setupControllers($folder);

        $this->setupRequests($folder);

        $this->setupRules($folder);

        $this->setupDataObjects($folder);

        $this->setupEnums($folder);

        $this->setupExceptions($folder);

        $this->setupCqrs($folder);

        $this->setupModels($folder);

        $this->setupMails($folder);

        $this->installPackages();

        // Tests...
        if ( ! $this->installAuthenticationTests($folder)) {
            return 1;
        }

        $this->components->info('Auth module scaffolding installed successfully.');

        return 0;
    }

    private function installAuthenticationTests(): bool
    {
        if ( ! $this->option('pest')) {
            return false;
        }

        $files = new Filesystem();

        $files->ensureDirectoryExists(base_path('tests/Feature'));

        $this->removeComposerPackages(['phpunit/phpunit'], true);

        if ( ! $this->requireComposerPackages(['pestphp/pest:^2.0', 'pestphp/pest-plugin-laravel:^2.0'], true)) {
            return false;
        }

        $files->copyDirectory(__DIR__ . '/../../stubs/auth/tests/Feature', base_path('tests/Feature'));
        $files->copy(__DIR__ . '/../../stubs/auth/tests/Pest.php', base_path('tests/Pest.php'));
        $files->copy(__DIR__ . '/../../stubs/auth/tests/Helpers.php', base_path('tests/Helpers.php'));

        return true;
    }

    private function installPackages(): void
    {
        $packages = [
            'thuraaung2493/otp-generator:dev-main',
            'thuraaung2493/laravel-make-files-tools:dev-main',
            'thuraaung2493/laravel-api-helpers:dev-main',
            'thuraaung2493/space-file-storage:dev-main',
        ];

        if ( ! $this->checkExistsComposerPackages(['laravel/sanctum'])) {
            array_push($packages, 'laravel/sanctum');
        }

        $this->requireComposerPackages($packages);
    }

    private function setupLangFile(string $folder): void
    {
        $this->copyFile(
            source: __DIR__ . '/../../stubs/' . $folder . '/lang/en/auth.php',
            target: base_path('lang/en/auth.php'),
        );
    }

    private function setupRoutes(string $folder): void
    {
        // Providers
        $this->copyFile(
            source: __DIR__ . '/../../stubs/auth/app/Providers/RouteServiceProvider.php',
            target: app_path('Providers/RouteServiceProvider.php'),
        );

        $this->copyDirectory(
            source: __DIR__ . '/../../stubs/' . $folder . '/routes/api',
            target: base_path('routes/api')
        );
    }

    private function setupControllers(string $folder): void
    {
        $this->copyDirectory(
            source: __DIR__ . '/../../stubs/' . $folder . '/app/Http/Controllers/Auth',
            target: app_path('Http/Controllers/Auth')
        );
    }

    private function setupRequests(string $folder): void
    {
        // Traits
        $this->copyDirectory(
            source: __DIR__ . '/../../stubs/auth/app/Http/Requests/Concerns',
            target: app_path('Http/Requests/Concerns'),
        );

        $this->copyDirectory(
            source: __DIR__ . '/../../stubs/' . $folder . '/app/Http/Requests/Auth',
            target: app_path('Http/Requests/Auth'),
        );
    }

    private function setupRules(): void
    {
        $this->copyFile(
            source: __DIR__ . '/../../stubs/auth/app/Rules/CheckEmailLoginUnique.php',
            target: app_path('Rules/CheckEmailLoginUnique'),
        );
    }

    private function setupDataObjects(string $folder): void
    {
        $this->copyDirectory(
            source: __DIR__ . '/../../stubs/' . $folder . '/app/DataObjects/Auth',
            target: app_path('DataObjects/Auth'),
        );
    }

    private function setupEnums(string $folder): void
    {
        $this->copyFile(
            source: __DIR__ . '/../../stubs/' . $folder . '/app/Enums/LoginType.php',
            target: app_path('Enums/LoginType.php'),
        );
    }

    private function setupExceptions(): void
    {
        $this->copyDirectory(
            source: __DIR__ . '/../../stubs/auth/app/Exceptions/Concerns',
            target: app_path('Exceptions/Concerns'),
        );

        if ($this->option('email')) {
            $this->copyDirectory(
                source: __DIR__ . '/../../stubs/auth/app/Exceptions/Auth',
                target: app_path('Exceptions/Auth'),
            );
        }
    }

    private function setupCqrs(string $folder): void
    {
        // Commands
        $this->copyDirectory(
            source: __DIR__ . '/../../stubs/' . $folder . '/app/Commands/Auth',
            target: app_path('Commands/Auth'),
        );

        // Queries
        $this->copyDirectory(
            source: __DIR__ . '/../../stubs/' . $folder . '/app/Queries/Users',
            target: app_path('Queries/Auth'),
        );
    }

    private function setupUserBuildersDir(): void
    {
        $this->copyFile(
            source: __DIR__ . '/../../stubs/auth/app/Builders/UserBuilder.php',
            target: app_path('Builders/UserBuilder.php'),
        );
        $this->copyFile(
            source: __DIR__ . '/../../stubs/auth/app/Builders/Concerns/HasCustomUserBuilder.php',
            target: app_path('Builders/Concerns/HasCustomUserBuilder.php'),
        );

        $this->replaceInFile(
            "namespace App\Models;" . PHP_EOL,
            "namespace App\Models;" . PHP_EOL . PHP_EOL . "use App\Builders\Concerns\HasCustomUserBuilder;",
            app_path('Models/User.php'),
        );
        $this->replaceInFile(
            "class User extends Authenticatable" . PHP_EOL . "{",
            "class User extends Authenticatable" . PHP_EOL . "{" . PHP_EOL . "use HasCustomUserBuilder;",
            app_path('Models/User.php'),
        );
    }

    private function setupModels(): void
    {
        // Migrations
        $this->copyFile(
            source: __DIR__ . '/../../stubs/auth/databases/migrations/0000_00_00_000000_create_users_table.php',
            target: database_path('factories/' . now()->format('Y_m_d_His') . '_create_users_table.php'),
        );
        $this->copyFile(
            source: __DIR__ . '/../../stubs/auth/databases/migrations/0000_00_00_000000_create_otps_table.php',
            target: database_path('factories/' . now()->format('Y_m_d_His') . '_create_otps_table.php'),
        );

        // Models
        $this->copyFile(
            source: __DIR__ . '/../../stubs/auth/app/Models/Otp.php',
            target: app_path('Models/Otp.php'),
        );

        // Factory
        $this->copyFile(
            source: __DIR__ . '/../../stubs/auth/databases/factories/OtpFactory.php',
            target: database_path('factories/OtpFactory.php'),
        );
        $this->copyFile(
            source: __DIR__ . '/../../stubs/auth/databases/factories/UserFactory.php',
            target: database_path('factories/UserFactory.php'),
        );
    }

    private function setupMails(): void
    {
        $this->copyFile(
            source: __DIR__ . '/../../stubs/auth/app/Mail/SendOtpCode.php',
            target: app_path('Mail/SendOtpCode.php'),
        );

        $this->copyFile(
            source: __DIR__ . '/../../stubs/auth/resources/views/emails/send-otp.blade.php',
            target: resource_path('views/emails/send-otp.blade.php'),
        );
    }

    protected function removeScaffoldingUnnecessaryForAuthModule(): void
    {
        $files = new Filesystem();

        // Remove routes files...
        $files->delete(base_path('routes/api.php'));
        $files->delete(base_path('routes/channel.php'));
        $files->delete(base_path('routes/console.php'));
        $files->delete(base_path('routes/web.php'));

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
