<?php

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
        $output->writeln('<info>Compiled po files</info>');

        return 0;
    }
}
