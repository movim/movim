<?php
namespace Movim\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ConfigCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('config')
            ->setDescription('Change the configuration')
            ->addOption(
               'info',
               null,
               InputOption::VALUE_REQUIRED,
               'Content of the info box on the login page'
            )
            ->addOption(
               'username',
               null,
               InputOption::VALUE_REQUIRED,
               'Username for the admin area'
            )
            ->addOption(
               'password',
               null,
               InputOption::VALUE_REQUIRED,
               'Password for the admin area'
            )
            ->addOption(
               'timezone',
               null,
               InputOption::VALUE_REQUIRED,
               'The server timezone'
            )
            ->addOption(
               'loglevel',
               null,
               InputOption::VALUE_REQUIRED,
               'The server loglevel, default 0'
            )
            ->addOption(
               'locale',
               null,
               InputOption::VALUE_REQUIRED,
               'The server main locale'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $cd = new \Modl\ConfigDAO();
        $config = $cd->get();

        foreach($input->getOptions() as $key => $value) {
            if(property_exists($config, $key) && isset($value)) {
                if($key == 'password') $value = sha1($value);

                $old = $config->$key;
                $config->$key = $value;

                $cd->set($config);
                $output->writeln(
                    '<info>The configuration key</info> '.
                    $key.
                    ' <info>has been updated from</info> '.
                    $old.
                    ' <info>to</info> '.
                    $value);
            }
        }
    }
}
