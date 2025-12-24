<?php

namespace LBHurtado\FormHandlerSignature\Console;

use Illuminate\Console\Command;

class InstallSignatureHandlerCommand extends Command
{
    protected $signature = 'signature-handler:install {--force}';

    protected $description = 'Install signature handler UI dependencies and assets';

    public function handle(): int
    {
        $this->info('Installing Signature Handler...');

        // UI dependencies (card, button, alert) should already be installed
        // No shadcn components needed to install

        // Publish Vue components
        $this->call('vendor:publish', [
            '--tag' => 'signature-handler-stubs',
            '--force' => $this->option('force'),
        ]);

        $this->info('✓ Signature Handler installed successfully!');

        return self::SUCCESS;
    }
}
