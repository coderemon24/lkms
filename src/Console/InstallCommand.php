<?php

namespace Lkms\Client\Console;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    protected $signature = 'lkms:install {--force : Overwrite existing configuration}';
    protected $description = 'Install and set up the LKMS Client Licensing package in your application';

    public function handle(): int
    {
        $this->info('====================================================');
        $this->info('  LKMS Software Licensing Client — Setup Wizard');
        $this->info('====================================================');

        // 1. Publish configuration
        $this->info('Publishing config/lkms.php...');
        $params = ['--tag' => 'lkms-config'];
        if ($this->option('force')) {
            $params['--force'] = true;
        }
        $this->call('vendor:publish', $params);

        // 2. Run migrations
        $this->info('Running database migrations for lkms_leases...');
        $this->call('migrate');

        $this->newLine();
        $this->info('✔ LKMS Client Package installed successfully!');
        $this->line('Next steps:');
        $this->line(' 1. Add \'license.enforce\' middleware to your web routes or bootstrap/app.php');
        $this->line(' 2. Visit: ' . url('/license/activate') . ' to activate your software');
        $this->line(' 3. Schedule \'php artisan lkms:heartbeat\' in routes/console.php');
        $this->info('====================================================');

        return self::SUCCESS;
    }
}
