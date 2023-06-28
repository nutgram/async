<?php

namespace SergiX44\Async;

use RuntimeException;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Common\Update;
use Throwable;

class ProcessManager
{

    private array $runners = [];

    public function __construct(private int $maxWorkers)
    {
    }

    public function pushUpdates(Nutgram $bot, mixed $stderr, array $updates)
    {
        $pid = pcntl_fork();

        if ($pid == -1) {
            throw new RuntimeException('Cannot fork!');
        } elseif ($pid) {
            $this->runners[$pid] = $pid;

            // remove the stopped runners
            foreach ($this->runners as $pid) {
                if (pcntl_waitpid($pid, $status, WNOHANG | WUNTRACED)) {
                    if (pcntl_wifexited($status)) {
                        unset($this->runners[$pid]);
                    }
                }
            }
        } else {
            $this->forkRunners($bot, $stderr, $updates);
        }
    }

    public function forkRunners(Nutgram $bot, mixed $stderr, array $updates)
    {
        $updateWorkers = [];
        foreach ($updates as $update) {
            if (count($updateWorkers) >= $this->maxWorkers) {
                $pid = pcntl_wait($status);
                unset($updateWorkers[$pid]);
            }

            $pid = pcntl_fork();

            if ($pid == -1) {
                throw new RuntimeException('Cannot fork!');
            } elseif ($pid) {
                $updateWorkers[$pid] = $pid;
            } else {
                try {
                    $bot->processUpdate($update);
                } catch (Throwable $e) {
                    fwrite($stderr, "$e\n");
                } finally {
                    exit(0);
                }
            }
        }

        foreach ($updateWorkers as $pid) {
            pcntl_waitpid($pid, $status);
        }
        exit(0);
    }

}