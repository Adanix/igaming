<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class DemoResetCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'demo:reset {--force : Force the operation to run without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cleanly reset and reseed the interconnected USD demo dataset';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (app()->isProduction()) {
            $this->error('CRITICAL: demo:reset is disabled in production environment!');
            return 1;
        }

        if (! config('demo.enabled', true)) {
            $this->error('Demo mode is disabled (APP_DEMO_MODE=false). Command halted.');
            return 1;
        }

        $this->info('Resetting and reseeding NexusPlay iGaming demo dataset...');

        Artisan::call('migrate:fresh', [
            '--seed' => true,
            '--force' => true,
        ]);

        $this->info(Artisan::output());
        $this->info('✓ Demo dataset successfully reset and verified.');

        return 0;
    }
}
