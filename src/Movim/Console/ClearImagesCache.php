<?php
/*
 * SPDX-FileCopyrightText: 2026 Jaussoin Timothée
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Movim\Console;

use App\Contact;
use App\Info;
use App\Presence;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class ClearImagesCache extends Command
{
    protected function configure()
    {
        $this
            ->setName('clearImagesCache')
            ->setDescription('Clear all the images from the cache and the database')
            ->addOption(
                'force',
                'f',
                InputOption::VALUE_OPTIONAL,
                'Force without asking questions'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $imagesPath = glob(PUBLIC_CACHE_PATH . '*.jpg', GLOB_NOSORT);
        $imagesPath = array_merge($imagesPath, glob(PUBLIC_CACHE_PATH . '*.webp', GLOB_NOSORT));

        $helper = new QuestionHelper;
        $question = new ConfirmationQuestion(count($imagesPath) . ' images will be deleted from the file cache and from the database, Movim will have to redownload them from the XMPP network after the daemon restart, are you sure about this [y/N] ? ', false);

        if ($helper->ask($input, $output, $question)) {
            // Files
            $output->writeln('<info>🧹 Removing all the files</info>');
            foreach ($imagesPath as $imagePath) {
                @unlink($imagePath);
            }

            // Database
            $output->writeln('<info>🔧 Clearing the contacts table</info>');
            Contact::query()->update(['avatartype' => null, 'avatarhash' => null, 'bannerhash' => null]);

            $output->writeln('<info>🔧 Clearing the infos table</info>');
            Info::query()->update(['avatarhash' => null]);

            // Stickers
            $output->writeln('<info>🗜️  Recompiling the stickers</info>');
            $command = $this->getApplication()->find('compileStickers');
            $command->run(new ArrayInput(['command' => 'compileStickers']), $output);

            $output->writeln('<info>✨ All good, please restart your daemon to rebuild the caches</info>');

            return Command::SUCCESS;
        } else {
            $output->writeln('<error>Aborting</error>');
        }

        return Command::FAILURE;
    }
}
