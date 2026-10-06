<?php

namespace Lkms\Client\Console;

use Illuminate\Console\Command;
use Lkms\Client\Services\LicenseClientService;

class HeartbeatCommand extends Command
{
    protected $signature = 'lkms:heartbeat';
    protected $description = 'Sync software license lease heartbeat with central LKMS server';

    public function handle(LicenseClientService $client): int
    {
        $this->info('Connecting to central LKMS authority for lease renewal...');
        $result = $client->heartbeat();

        if ($result['success']) {
            $this->info("✔ {$result['message']}");
            return self::SUCCESS;
        }

        $this->warn("! {$result['message']}");
        return self::FAILURE;
    }
}
