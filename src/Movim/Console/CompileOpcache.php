<?php
/*
 * SPDX-FileCopyrightText: 2010 Jaussoin Timothée
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Movim\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\ProgressBar;

class CompileOpcache extends Command
{
    protected function configure()
    {
        $this
            ->setName('compileOpcache')
            ->setDescription('Compile and cache PHP files in Opcache');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (isOpcacheEnabled()) {
            $output->writeln('<info>Opcache is enabled compiling files...</info>');

            $filesCount = count(listOpcacheCompilableFiles());

            ProgressBar::setFormatDefinition('custom', '%current%/%max% | %message%');
            $progressBar = new ProgressBar($output, $filesCount);
            $progressBar->setFormat('custom');

            foreach (compileOpcache() as $file) {
                $progressBar->setMessage('Compiling...');
                $progressBar->advance();
            }

            $progressBar->setMessage('Files compiled');
            $progressBar->advance();
            $output->writeln("");
        } else {
            $output->writeln('<error>Opcache is disabled, it is strongly advised to enable it in PHP CLI php.ini</error>');
            $output->writeln('Set opcache.enable=1 and opcache.enable_cli=1');
        }

        return Command::SUCCESS;
    }
}
