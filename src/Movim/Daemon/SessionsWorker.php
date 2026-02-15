<?php

namespace Movim\Daemon;

use App\Session;
use Movim\Daemon\Session as DaemonSession;
use React\EventLoop\LoopInterface;
use React\ChildProcess\Process;
use Ratchet\ConnectionInterface;

class SessionsWorker
{
    public string $id;
    public ?Process $process = null;
    public ?ConnectionInterface $internalSocket = null;
    private array $sessions = [];

    public function __construct(
        private LoopInterface $loop,
        protected string $baseuri,
        private string $key, // Daemon secure key
    ) {
        $this->id = \generateKey(16);
        $this->register($loop);
    }

    public function attachInternal(ConnectionInterface $conn)
    {
        $this->internalSocket = $conn;

        if (config('daemon.verbose')) {
            echo colorize('🔧 ' . $this->id, 'turquoise') . ": " . colorize($conn->resourceId . " internal connected\n", 'green');
        }

        /**
         * If some sessions were already attached before the worker was launched we spawn
         * their linker in the worker
         */
        foreach ($this->sessions as $session) {
            $session->spawnLinker();
        }
    }

    public function messageOut($message)
    {
        foreach ($this->sessions as $session) {
            $session->messageOut($message);
        }
    }

    public function newSession(string $sid, ConnectionInterface $connection)
    {
        if (!$this->hasSession($sid)) {
            if (config('daemon.verbose')) echo colorize($this->id, 'turquoise') . ' ' . colorize("new session\n", 'green');
            $this->sessions[$sid] = new DaemonSession(
                worker: $this,
                sid: $sid,
                baseuri: $this->baseuri,
                language: $this->getLanguage($connection)
            );

            if (config('daemon.verbose')) echo colorize($this->id, 'turquoise') . ' ' . colorize("attach connection to session\n", 'green');
            $this->attachSession($sid, $connection);

            // If we already have an attached worker
            if ($this->internalSocket) {
                $this->sessions[$sid]->spawnLinker();
            }
        }
    }

    public function getSessions(): array
    {
        return $this->sessions;
    }

    public function getSession(string $sid): ?DaemonSession
    {
        return $this->sessions[$sid];
    }

    public function hasSession(string $sid): bool
    {
        return array_key_exists($sid, $this->sessions);
    }

    public function attachSession(string $sid, ConnectionInterface $connection)
    {
        $this->sessions[$sid]->attach($connection);
    }

    public function countSessions(): int
    {
        return count($this->sessions);
    }

    public function closeSession(string $sid)
    {
        Session::where('id', $sid)->delete();
        unset($this->sessions[$sid]);
    }

    public function close()
    {
        if (config('daemon.verbose')) {
            echo colorize('🔧 ' . $this->id, 'turquoise') . ": " . colorize("closing the worker\n", 'green');
        }

        if ($this->process) {
            $this->process->terminate();
            $this->process = null;
        }
    }

    /*public function cleanSessions()
    {
        foreach ($this->sessions as $sid => $session) {
            if (
                $session->countClients() == 0
                && $session->registered == false
            ) {
                $session->killLinker();
            }

            if ($session->process == null) {
                unset($this->sessions[$sid]);
            }
        }
    }*/

    private function register(LoopInterface $loop)
    {
        // Only load the required extensions
        $configuration = '-n ';

        foreach (requiredExtensions() as $extension) {
            $configuration .= '-dextension=' . $extension . '.so ';
        }

        // Enable Opcache
        if (isOpcacheEnabled()) {
            $configuration .= '-dzend_extension=opcache.so -dopcache.enable=1 -dopcache.enable_cli=1 ';
        }

        // Launching the linker
        $this->process = new Process(
            'exec ' . PHP_BINARY . ' ' . $configuration . ' -d=memory_limit=512M sessionsworker.php ',
            cwd: WORKERS_PATH,
            env: [
                'wid'           => $this->id,
                'baseuri'       => $this->baseuri,
                'DAEMON_DEBUG'  => config('daemon.debug'),
                'DAEMON_PORT'   => config('daemon.port'),
                'DAEMON_VERBOSE' => config('daemon.verbose'),
                'DB_DATABASE'   => config('database.database'),
                'DB_DRIVER'     => config('database.driver'),
                'DB_HOST'       => config('database.host'),
                'DB_PASSWORD'   => config('database.password'),
                'DB_PORT'       => config('database.port'),
                'DB_USERNAME'   => config('database.username'),
                'key'           => $this->key,
            ]
        );
        $this->process->start($loop);

        // The linker died, we close properly the session
        $this->process->on('exit', function ($output) {
            if (config('daemon.verbose')) {
                echo colorize($this->id, 'yellow') . " : " . colorize("sessionsworker killed \n", 'red');
            }

            $this->process = null;
            foreach ($this->sessions as $session) {
                $session->close();
            }
        });

        $this->process->stderr->on('data', function (string $message) {
            echo $message;
        });
    }

    private function getLanguage(ConnectionInterface $connection)
    {
        $languages = $connection->httpRequest->getHeader('Accept-Language');
        return (is_array($languages) && !empty($languages)) ? $languages[0] : false;
    }
}
