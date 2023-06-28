<?php

namespace SergiX44\Async;

use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\RunningMode\Polling;
use Throwable;

class ParallelPolling extends Polling
{
    private ProcessManager $manager;

    public function __construct(int $concurrency = 2)
    {
        $this->manager = new ProcessManager($concurrency);
    }

    protected function fire(Nutgram $bot, array $updates = []): void
    {
        $this->manager->pushUpdates($bot, self::$STDERR, $updates);
    }
}
