<?php

namespace Fukibay\StarterPack\Console;

use Illuminate\Console\Command;

class PingCommand extends Command
{
    protected $signature = 'fukibay:ping';
    protected $description = 'Check if Fukibay Starter Pack is registered correctly';

    public function handle(): int
    {
        $this->info('✅ Fukibay Starter Pack is alive.');
        return self::SUCCESS;
    }
}
