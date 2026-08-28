<?php
/*
 * SPDX-FileCopyrightText: 2010 Jaussoin Timothée
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Movim\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GalenerManager extends Command
{
    protected function configure()
    {
        $this
            ->setName('galener')
            ->setDescription('Manage the Galener worker')
            ->addArgument(
                name: 'action',
                mode: InputArgument::REQUIRED,
                description: 'Action to perform',
                suggestedValues: ['start', 'stop', 'restart']
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $message = match ($input->getArgument('action')) {
            'start' => requestAPI('galenerstart'),
            'stop' => requestAPI('galenerstop'),
            'restart' => requestAPI('galenerrestart'),
            'status' => requestAPI('galenerstatus'),
            default => requestAPI('galenerstatus')
        };

        $output->writeln('<info>' . $message . '</info>');

        return Command::SUCCESS;
    }
}
