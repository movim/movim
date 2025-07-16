<?php
/*
 * SPDX-FileCopyrightText: 2023 Jaussoin Timothée
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Movim\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ClearTemplatesCache extends Command
{
    protected function configure()
    {
        $this
            ->setName('clearTemplatesCache')
            ->setDescription('Clear the internal templates cache');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        foreach (glob(
            CACHE_PATH .
                '*.rtpl.*',
            GLOB_NOSORT
        ) as $cacheFile) {
            @unlink($cacheFile);
        }

        $output->writeln('<info>Template cache cleared</info>');

        return Command::SUCCESS;
    }
}
