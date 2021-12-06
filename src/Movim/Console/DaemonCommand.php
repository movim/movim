<?php
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
use Movim\i18n\Locale;
use App\User;

use Phinx\Migration\Manager;
use Phinx\Config\Config;
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
                'url',
                'u',
                InputOption::VALUE_OPTIONAL,
                'Public URL of your Movim instance'
            )
            ->addOption(
                'port',
                'p',
                InputOption::VALUE_OPTIONAL,
                'Port on which the daemon will listen',
                8080
            )
            ->addOption(
                'interface',
                'i',
                InputOption::VALUE_OPTIONAL,
                'Interface on which the daemon will listen',
                '127.0.0.1'
            )
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

        $loop = Loop::get();

        if ($input->getOption('url') && Validator::url()->notEmpty()->validate($input->getOption('url'))) {
            $baseuri = rtrim($input->getOption('url'), '/') . '/';
        } elseif (file_exists(CACHE_PATH.'baseuri')) {
            $baseuri = file_get_contents(CACHE_PATH.'baseuri');
        } else {
            $output->writeln('<comment>Please load the login page once before starting the daemon to cache the public URL</comment>');
            $output->writeln('<comment>or force a public URL using the --url parameter</comment>');
            exit;
        }

        if (User::where('admin', true)->count() == 0) {
            $output->writeln('<comment>Please set at least one user as an admin once its account is logged in</comment>');

            $output->writeln('<info>To set an existing user admin</info>');
            $output->writeln('<info>php daemon.php setAdmin {jid}</info>'."\n");
        }

        $locale = Locale::start();

        $locale->compileIni();
        $output->writeln('<info>Compiled hash file</info>');
        $locale->compilePos();
        $output->writeln('<info>Compiled po files</info>');

        $count = compileStickers();
        $output->writeln('<info>'.$count.' stickers compiled</info>');

        $output->writeln('<info>Movim daemon launched</info>');
        $output->writeln('<info>Base URL: '.$baseuri.'</info>');

        if ($input->getOption('debug')) {
            $output->writeln("\n".'<comment>Debug is enabled, check the logs in syslog or '.DOCUMENT_ROOT.'/log/</comment>');
        }

        $core = new Core($loop, $baseuri, $input);
        $app  = new HttpServer(new WsServer($core));

        $socket = new SocketServer(
            $input->getOption('interface').':'.$input->getOption('port')
        );

        $socketApi = new SocketServer('unix://' . API_SOCKET);
        new Api($socketApi, $core);

        (new IoServer($app, $socket, $loop))->run();

        return 0;
    }
}
