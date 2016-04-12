<?php
namespace Movim\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DatabaseCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('db')
            ->setDescription('Database updater')
            ->addOption(
               'set',
               's',
               InputOption::VALUE_NONE,
               'Will apply updates on the database'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $md = \Modl\Modl::getInstance();

        if($input->getOption('set')) {
            $md->check(true);
            $output->writeln('<info>Database updated</info>');
        } else {
            $toDo = $md->check();
            if($toDo != null) {
                $output->writeln('<comment>The database needs to be updated</comment>');
                foreach($toDo as $do) {
                    $output->writeln('<question>'.$do.'</question>');
                }
            } else {
                $output->writeln('<info>Nothing to do here</info>');
            }
        }
    }
}
