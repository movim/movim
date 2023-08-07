<?php
/*
 * SPDX-FileCopyrightText: 2010 Jaussoin Timothée
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Movim\Console;

use Movim\Image;
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
        $count = 0;

        foreach (glob(PUBLIC_PATH . '/stickers/*/*.png', GLOB_NOSORT) as $path) {
            $key = basename($path, '.png');

            if ($key != 'icon') {
                $count++;
                copy($path, PUBLIC_CACHE_PATH . hash(Image::$hash, $key) . '_o.png');
            }
        }

        $output->writeln('<info>'.$count.' stickers compiled</info>');
        return 0;
    }
}
