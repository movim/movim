<?php

namespace Movim\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

use App\User;

class SetAdmin extends Command
{
    protected function configure()
    {
        $this
            ->setName('setAdmin')
            ->setDescription('Set an account admin')
            ->addArgument('jid', InputArgument::REQUIRED, 'User Jabber ID');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $user = User::find($input->getArgument('jid'));

        if ($user) {
            $user->admin = true;
            $user->save();
            $output->writeln('<info>User '.$input->getArgument('jid').' is now admin</info>');

            return 0;
        }

        $output->writeln('<error>User '.$input->getArgument('jid').' not found</error>');

        return 1;
    }
}
