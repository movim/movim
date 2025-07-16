<?php
/*
 * SPDX-FileCopyrightText: 2010 Jaussoin Timothée
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Movim\Console;

use App\Configuration;

use Symfony\Component\Console\Command\Command;
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
                'description',
                null,
                InputOption::VALUE_REQUIRED,
                'General description of the instance'
            )
            ->addOption(
                'timezone',
                null,
                InputOption::VALUE_REQUIRED,
                'The server timezone'
            )
            ->addOption(
                'restrictsuggestions',
                null,
                InputOption::VALUE_REQUIRED,
                'Only suggest chatrooms, Communities and other contents that are available on the user XMPP server and related services'
            )
            ->addOption(
                'chatonly',
                null,
                InputOption::VALUE_REQUIRED,
                'Disable all the social feature (Communities, Blog…) and keep only the chat ones'
            )
            ->addOption(
                'disableregistration',
                null,
                InputOption::VALUE_REQUIRED,
                'Remove the XMPP registration flow and buttons from the interface'
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
            ->addOption(
                'xmppdomain',
                null,
                InputOption::VALUE_REQUIRED,
                'The default XMPP server domain'
            )
            ->addOption(
                'xmppdescription',
                null,
                InputOption::VALUE_REQUIRED,
                'The default XMPP server description'
            )
            ->addOption(
                'xmppwhitelist',
                null,
                InputOption::VALUE_REQUIRED,
                'The whitelisted XMPP servers'
            )
            ->addOption(
                'gifapikey',
                null,
                InputOption::VALUE_REQUIRED,
                'Tenor API key'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $configuration = Configuration::get();

        foreach ($input->getOptions() as $key => $value) {
            if (in_array($key, $configuration->fillable) && isset($value)) {
                $old = $configuration->$key;
                $configuration->$key = $value;
                $configuration->save();

                $output->writeln(
                    '<info>The configuration key</info> '.
                    $key.
                    ' <info>has been updated from</info> '.
                    $old.
                    ' <info>to</info> '.
                    $configuration->$key
                );
            }
        }

        return Command::SUCCESS;
    }
}
