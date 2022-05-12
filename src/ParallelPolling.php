<?php

namespace SergiX44\Async;

use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\RunningMode\Polling;
use Spatie\Async\Pool;

class ParallelPolling extends Polling
{
    private Pool $pool;

    public function __construct()
    {
        $this->pool = Pool::create()
            ->concurrency(4);
    }

    protected function fire(Nutgram $bot, array|null $updates): void
    {
        $this->pool->add(function () use ($bot, $updates) {
            parent::fire($bot, $updates);
        });
    }

    public function __destruct()
    {
        $this->pool->stop();
        $this->pool->wait();
    }
}
