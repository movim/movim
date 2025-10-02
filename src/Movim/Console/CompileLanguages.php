<?php
/*
 * SPDX-FileCopyrightText: 2010 Jaussoin Timothée
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Movim\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Movim\i18n\Locale;

class CompileLanguages extends Command
{
    protected function configure()
    {
        $this
            ->setName('compileLanguages')
            ->setDescription('Compile and cache the languages files');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $locale = Locale::start();

        $locale->compileIni();
        $output->writeln('<info>Compiled hash file</info>');
        $locale->compilePos();
        $output->writeln('<info>po files compiled</info>');

        return Command::SUCCESS;
    }
}
