<?php
/*
 * SPDX-FileCopyrightText: 2010 Jaussoin Timothée
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Movim\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Respect\Validation\Validator;

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

use Movim\Daemon\Core;
use Movim\Daemon\Api;
use App\User;

use Phinx\Migration\Manager;
use Phinx\Config\Config;
use React\ChildProcess\Process;
use Symfony\Component\Console\Output\NullOutput;

use React\EventLoop\Loop;
use React\Socket\SocketServer;

class DaemonCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('start')
            ->setDescription('Start the daemon')
            ->addOption(
                'debug',
                'd',
                InputOption::VALUE_NONE,
                'Output XMPP logs'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $config = new Config(require(DOCUMENT_ROOT . '/phinx.php'));
        $manager = new Manager($config, $input, new NullOutput);

        if ($manager->printStatus('movim')['hasDownMigration']) {
            $output->writeln('<comment>The database needs to be migrated before running the daemon</comment>');
            $output->writeln('<info>To migrate the database run</info>');
            $output->writeln('<info>php vendor/bin/phinx migrate</info>');
            exit;
        }

        foreach (requiredExtensions() as $extension) {
            if (!extension_loaded($extension)) {
                $output->writeln('<comment>The following PHP extension is missing: ' . $extension . '</comment>');
                return Command::FAILURE;
            }
        }

        $loop = Loop::get();

        if (config('daemon.url')) {
            # Underlying `Validator`, URL validation is done via
            # FILTER_VALIDATE_URL, where the FILTER_FLAG_SCHEME_REQUIRED was
            # deprecated & then removed in later PHP versions now *reqiring* a
            # scheme. Base URIs are allowed to be scheme-less which helps users
            # looking to support both HTTP & HTTPS (either for testing purposes
            # or otherwise). As such, we can append a missing scheme in order
            # satisfy this validator requirement, but still use the scheme-less
            # URI in practice.
            $schemeEnforcedURL = parse_url(config('daemon.url'), PHP_URL_SCHEME)
                ? config('daemon.url')
                : 'http://' . ltrim(config('daemon.url'), '/');
            if (Validator::url()->notEmpty()->isValid($schemeEnforcedURL)) {
                $baseuri = rtrim(config('daemon.url'), '/') . '/';
            }
        } else {
            $output->writeln('<comment>Please configure DAEMON_URL in .env</comment>');
            exit;
        }

        if (User::where('admin', true)->count() == 0) {
            $output->writeln('<comment>Please set at least one user as an admin once its account is logged in</comment>');

            $output->writeln('<info>To set an existing user admin</info>');
            $output->writeln('<info>php daemon.php setAdmin {jid}</info>' . "\n");
        }


        $clearTemplatesCache = new Process('exec ' . PHP_BINARY . ' daemon.php clearTemplatesCache');
        $clearTemplatesCache->start($loop);
        $clearTemplatesCache->on('exit', fn($out) => $output->writeln('<info>Templates cache cleared</info>'));

        $compileLanguages = new Process('exec ' . PHP_BINARY . ' daemon.php compileLanguages');
        $compileLanguages->start($loop);
        $compileLanguages->on('exit', fn($out) => $output->writeln('<info>Compiled po files</info>'));

        $compileStickers = new Process('exec ' . PHP_BINARY . ' daemon.php compileStickers');
        $compileStickers->start($loop);
        $compileStickers->on('exit', fn($out) => $output->writeln('<info>Stickers compiled</info>'));

        $output->writeln('<info>Movim daemon launched</info>');
        $output->writeln('<info>Base URL: ' . $baseuri . '</info>');

        if ($input->getOption('debug')) {
            $output->writeln("\n" . '<comment>Debug is enabled, check the logs in syslog or ' . DOCUMENT_ROOT . '/log/</comment>');
        }

        if (isOpcacheEnabled()) {
            $compileOpcache = new Process('exec ' . PHP_BINARY . ' daemon.php compileOpcache');
            $compileOpcache->start($loop);
            $compileOpcache->on('exit', fn($out) => $output->writeln('<info>Files compiled in Opcache</info>'));
        } else {
            $output->writeln('<error>Opcache is disabled, it is strongly advised to enable it in PHP CLI php.ini</error>');
            $output->writeln('Set opcache.enable=1 and opcache.enable_cli=1 in the PHP CLI ini file');
        }

        $core = new Core($loop, $baseuri);
        $app  = new HttpServer(new WsServer($core));

        $socket = new SocketServer(
            config('daemon.interface') . ':' . config('daemon.port')
        );

        $socketApi = new SocketServer('unix://' . API_SOCKET);
        new Api($socketApi, $core);

        $resolverWorker = new Process('exec ' . PHP_BINARY . ' resolver.php');
        $resolverWorker->start($loop);
        $resolverWorker->on('exit', fn() => $output->writeln('<error>Resolver Worker crashed</error>'));
        $output->writeln('<info>Resolver Worker launched</info>');

        $templaterWorker = new Process(
            'exec ' . PHP_BINARY . ' templater.php',
            null,
            [
                'key'       => $core->getKey(),
                'baseuri'   => $baseuri,
                'port'      => config('daemon.port')
            ]
        );
        $templaterWorker->start($loop);
        $templaterWorker->on('exit', fn() => $output->writeln('<error>Templater Worker crashed</error>'));
        $output->writeln('<info>Templater Worker launched</info>');

        (new IoServer($app, $socket, $loop))->run();

        return Command::SUCCESS;
    }
}
