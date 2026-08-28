<?php

namespace Movim\Daemon;

use React\ChildProcess\Process;
use React\EventLoop\LoopInterface;
use React\EventLoop\TimerInterface;
use Respect\Validation\Validator;
use Symfony\Component\Console\Output\OutputInterface;

class GalenerManager
{
    private ?Process $galenerWorker = null;
    private ?TimerInterface $restartTimer;
    private const GALENER_WORKER_CONSOLE = '📞 Galener Worker: ';
    public function __construct(private LoopInterface $loop, private OutputInterface $output) {}

    public function start(): string
    {
        if ($this->galenerWorker != null) {
            $message = 'Already started';
            $this->output->writeln('<info>' . self::GALENER_WORKER_CONSOLE . $message . '</info>');
            return $message;
        }

        if (
            config('galener.xmpp_host')
            && Validator::domain()->validate(config('galener.xmpp_host'))
            && !empty(config('galener.xmpp_password'))
        ) {
            if (empty(config('galener.galene_path')) || !file_exists(config('galener.galene_path'))) {
                $message = 'galene executable not accessible';
                $this->output->writeln('<comment>' . self::GALENER_WORKER_CONSOLE . $message . '</comment>');
                return $message;
            } else {
                $this->galenerWorker = new Process('exec ' . PHP_BINARY . ' galener.php', cwd: WORKERS_PATH);
                $this->galenerWorker->start($this->loop);
                $this->galenerWorker->on('exit', function () {
                    $this->output->writeln('<info>' . self::GALENER_WORKER_CONSOLE . '🔴 Stopped</info>');
                    $this->galenerWorker = null;
                });
                $message = '🟢 Launched';
                $this->output->writeln('<info>' . self::GALENER_WORKER_CONSOLE . $message . '</info>');
                return $message;
            }
        } else {
            $message = 'Configuration empty or invalid';
            $this->output->writeln('<comment>' . self::GALENER_WORKER_CONSOLE . $message . '</comment>');
            return $message;
        }
    }

    public function stop(): string
    {
        if ($this->galenerWorker != null) {
            $message = '🟠 Stopping';
            $this->output->writeln('<info>' . self::GALENER_WORKER_CONSOLE . $message . '</info>');
            $this->galenerWorker->terminate(SIGTERM);

            $this->loop->addTimer(5.0, function () {
                if ($this->galenerWorker && $this->galenerWorker->isRunning()) {
                    $this->galenerWorker->terminate(SIGKILL);
                }
            });

            return $message;
        }

        $message = 'Not started';
        $this->output->writeln('<info>' . self::GALENER_WORKER_CONSOLE . $message . '</info>');
        return $message;
    }

    public function restart(): string
    {
        if ($this->galenerWorker != null) {
            $this->stop();

            $this->restartTimer = $this->loop->addPeriodicTimer(2, function () {
                if ($this->galenerWorker == null) {
                    $this->loop->cancelTimer($this->restartTimer);
                    $this->start();
                } else {
                    $this->output->writeln('<comment>' . self::GALENER_WORKER_CONSOLE . 'Waiting for the worker to stop to restart it…</comment>');
                }
            });

            return 'Trying to restart';
        }

        $message = 'Not started';
        $this->output->writeln('<info>' . self::GALENER_WORKER_CONSOLE . $message . '</info>');
        return $message;
    }

    public function status(): string
    {
        $message = $this->galenerWorker != null
            ? '🟢 Running'
            : '🔴 Not running';
        $this->output->writeln('<info>' . self::GALENER_WORKER_CONSOLE . $message . '</info>');
        return $message;
    }
}
