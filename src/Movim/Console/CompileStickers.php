<?php
/*
 * SPDX-FileCopyrightText: 2010 Jaussoin Timothée
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Movim\Console;

use App\Sticker;
use App\StickersPack;
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

        StickersPack::query()->delete();

        foreach (glob(PUBLIC_STICKERS_PATH . '*', GLOB_NOSORT) as $pack) {
            $parsed = parse_ini_file($pack . '/info.ini');
            $packName = basename($pack);

            $stickersPack = new StickersPack;
            $stickersPack->name = $packName;
            $stickersPack->homepage = $parsed['url'];
            $stickersPack->license = $parsed['license'];
            $stickersPack->author = $parsed['author'];
            $stickersPack->save();

            foreach (glob($pack . '/*.png', GLOB_NOSORT) as $path) {
                $key = basename($path, '.png');

                if ($key != 'icon') {
                    $hashed = hash(Image::$hash, file_get_contents($path));

                    $image = new Image;
                    $image->fromPath($path);
                    $image->setKey($hashed);
                    $image->save();

                    $sticker = new Sticker;
                    $sticker->pack = $packName;
                    $sticker->name = $key;
                    $sticker->filename = $key . '.png';
                    $sticker->cache_hash = $hashed;
                    $sticker->cache_hash_algorythm = Image::$hash;
                    $sticker->save();

                    $count++;
                }
            }

            $output->writeln('<info>' . $packName . ' compiled</info>');
        }

        $output->writeln('<info>' . $count . ' stickers compiled</info>');
        return Command::SUCCESS;
    }
}
