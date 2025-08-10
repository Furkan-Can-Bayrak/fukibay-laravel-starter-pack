<?php

namespace Fukibay\StarterPack;

use Illuminate\Support\ServiceProvider;
use Fukibay\StarterPack\Console\InstallCommand;
use Fukibay\StarterPack\Console\PingCommand;
use Fukibay\StarterPack\Console\MakeRepositoryCommand;
use Fukibay\StarterPack\Console\MakeServiceCommand;

class StarterPackServiceProvider extends ServiceProvider
{
    public function register(): void
    {
         $this->mergeConfigFrom(
            __DIR__.'/../config/fukibay-starter-pack.php', 'fukibay-starter-pack'
        );
    }

     public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallCommand::class,
                PingCommand::class,
                MakeRepositoryCommand::class,
                MakeServiceCommand::class,
            ]);

            $this->publishes([
                __DIR__.'/../config/fukibay-starter-pack.php' => config_path('fukibay-starter-pack.php'),
            ], 'fukibay-config');
        }
    }
}