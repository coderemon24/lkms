<?php

namespace Lkms\Client\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class EncodePackageCommand extends Command
{
    protected $signature = 'lkms:encode {--encoder=ioncube : Encoder to use: ioncube or sourceguardian}';
    protected $description = 'Encode and protect proprietary client licensing bytecode with ionCube or SourceGuardian';

    public function handle(): int
    {
        $encoder = strtolower($this->option('encoder'));
        $packageRoot = realpath(__DIR__ . '/../../');
        $srcDir = $packageRoot . '/src';
        $encodedDir = $packageRoot . '/dist/encoded-src';

        $this->info("=================================================");
        $this->info("  LKMS Proprietary Client Bytecode Encoder");
        $this->info("=================================================");
        $this->line("Package Source: {$srcDir}");
        $this->line("Target Output:  {$encodedDir}");

        if (!File::exists($encodedDir)) {
            File::makeDirectory($encodedDir, 0755, true);
        }

        if ($encoder === 'ioncube') {
            $cmd = "ioncube_encoder --into \"{$encodedDir}\" \"{$srcDir}\" --optimize max";
            $this->info("Generating ionCube compilation command:");
            $this->comment(" > {$cmd}");
        } else {
            $cmd = "sg_encoder --into \"{$encodedDir}\" \"{$srcDir}\"";
            $this->info("Generating SourceGuardian compilation command:");
            $this->comment(" > {$cmd}");
        }

        $this->newLine();
        $this->info("To execute directly, run scripts in: {$packageRoot}/build/");
        return self::SUCCESS;
    }
}
