<?php

declare(strict_types=1);

namespace Thuraaung\Startup\Console\Commands\Install;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Thuraaung\Startup\Console\Commands\Concerns\ComposerPackageManagement;
use Thuraaung\Startup\Console\Commands\Concerns\NodePackageManagement;
use Thuraaung\Startup\Console\Commands\Concerns\FileManagement;

final class InstallCommand extends Command
{
    use ComposerPackageManagement;
    use FileManagement;
    use InstallsAdminModule;
    use InstallsAuthModule;
    use NodePackageManagement;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'startup:install {module : The module that should be installed (auth,exception)}
                            {--email : Indicate that Email Register should be installed}
                            {--pest : Indicate that Pest should be installed}
                            {--composer=global : Absolute path to the Composer binary which should be used to install packages}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Startup a laravel project";

    protected array $modules = ['auth', 'admin'];

    public function handle(): int|null
    {
        if ('auth' === $this->argument('module')) {
            return $this->installAuthModule();
        } elseif ('admin' === $this->argument('module')) {
            return $this->installAdminModule();
        }

        $this->components->error('Invalid module. Supported modules are [auth,excetpion].');

        return Command::FAILURE;
    }

    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        if (null === $this->argument('module')) {
            $input->setArgument(
                name: 'module',
                value: $this->components->choice(
                    question: 'Which module would you like to install?',
                    choices: $this->modules,
                    default: 'auth',
                ),
            );
        }

        if ($this->isAuthModuleWithOption('pest')) {
            $input->setOption(
                name: 'pest',
                value: $this->components->confirm('Would you prefer to install Pest?'),
            );
        }

        if ($this->isAuthModuleWithOption('email')) {
            $input->setOption(
                name: 'email',
                value: $this->components->confirm('Would you prefer to install email register?'),
            );
        }
    }

    private function isAuthModuleWithOption(string $option): bool
    {
        return 'auth' === $this->argument('module') && ! $this->option($option);
    }
}
