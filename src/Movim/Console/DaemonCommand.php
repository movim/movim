<?php
namespace Movim\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Respect\Validation\Validator;

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

use Movim\Daemon\Core;
use Movim\Daemon\Api;
use Movim\Bootstrap;

use React\EventLoop\Factory;
use React\Socket\Server as Reactor;

class DaemonCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('start')
            ->setDescription('Start the daemon')
            ->addOption(
               'url',
               null,
               InputOption::VALUE_REQUIRED,
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
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $md = \Modl\Modl::getInstance();
        $infos = $md->check();

        if($infos != null) {
            $output->writeln('<comment>The database needs to be updated before running the daemon</comment>');

            $output->writeln('<info>To update the database run</info>');
            $output->writeln('<info>php mud.php db --set</info>');
            exit;
        }

        $loop = Factory::create();

        if(!Validator::url()->notEmpty()->validate($input->getOption('url'))) {
            $output->writeln('<error>Invalid or missing url parameter</error>');
            exit;
        }

        $baseuri = rtrim($input->getOption('url'), '/') . '/';

        $output->writeln('<info>Movim daemon launched</info>');
        $output->writeln('<info>Base URL: '.$baseuri.'</info>');

        $core = new Core($loop, $baseuri, $input);
        $app  = new HttpServer(new WsServer($core));

        $socket = new Reactor($loop);
        $socket->listen($input->getOption('port'), $input->getOption('interface'));

        $socketApi = new Reactor($loop);
        new Api($socketApi, $core);
        $socketApi->listen(1560);

        $server = new IoServer($app, $socket, $loop);

        $server->run();
    }
}
