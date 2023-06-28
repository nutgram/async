<?php

namespace SergiX44\Async;

use AsyncPHP\Doorman\Manager\ProcessManager;
use AsyncPHP\Doorman\Rule\InMemoryRule;
use AsyncPHP\Doorman\Task\ProcessCallbackTask;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\RunningMode\Polling;
use Throwable;

class ParallelPolling extends Polling
{
    private ProcessManager $manager;

    public function __construct(int $concurrency = 2)
    {
        $this->manager = new ProcessManager();
        $rule = new InMemoryRule();
        $rule->setProcesses($concurrency);
        $this->manager->addRule($rule);
    }

    protected function fire(Nutgram $bot, array $updates = []): void
    {
        foreach ($updates as $update) {
            $this->manager->addTask(new ProcessCallbackTask(static function () use ($bot, $update) {
                try {
                    $bot->processUpdate($update);
                } catch (Throwable $e) {
                    fwrite(self::$STDERR, "$e\n");
                } finally {
                    $bot->clear();
                }
            }));
        }

        $this->manager->tick();
    }
}
