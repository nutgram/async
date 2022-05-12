<?php

namespace SergiX44\Async;

use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\RunningMode\Polling;
use Spatie\Async\Pool;
use Spatie\Fork\Fork;
use Throwable;

class ParallelPolling extends Polling
{
    private Fork $pool;

    public function __construct(int $concurrency = 2)
    {
        $this->pool = Fork::new()->concurrent($concurrency);
    }

    protected function fire(Nutgram $bot, array|null $updates): void
    {
        $tasks = [];
        foreach ($updates as $update) {
            $tasks[] = static function () use ($bot, $update) {
                try {
                    $bot->processUpdate($update);
                } catch (Throwable $e) {
                    echo "$e\n";
                } finally {
                    $bot->clearData();
                }
            };
        }

        $this->pool->run($tasks);
    }
}
