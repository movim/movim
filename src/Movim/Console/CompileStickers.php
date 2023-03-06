<?php
/*
 * SPDX-FileCopyrightText: 2010 Jaussoin Timothée
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Movim\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CompileStickers extends Command
{
    protected function configure()
    {
        $this
            ->setName('compileStickers')
            ->setDescription('Compile and cache the stickers files');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $count = compileStickers();
        $output->writeln('<info>'.$count.' stickers compiled</info>');
        return 0;
    }
}
