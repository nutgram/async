<?php

namespace SergiX44\Async;

use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use SergiX44\Nutgram\Nutgram;

class ExtendsNutgramProvider extends ServiceProvider
{

    /**
     * @return void
     */
    public function register()
    {
        $this->app->extend(Nutgram::class, function (Nutgram $bot, Application $app) {
            if (!$app->runningUnitTests() && $app->runningInConsole()) {
                $concurrency = $bot->getConfig()['concurrency'] ?? 2;
                $bot->setRunningMode(new ParallelPolling($concurrency));
            }

            return $bot;
        });
    }

}
