<?php
/*
 * SPDX-FileCopyrightText: 2024 Jaussoin Timothée
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Movim\Console;

use App\Emoji;
use App\EmojisPack;
use Movim\Image;
use Respect\Validation\Validator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use ZipArchive;

use function React\Async\await;

class ImportEmojisPack extends Command
{
    protected function configure()
    {
        $this
            ->setName('importEmojisPack')
            ->setDescription('Add a custom emojis pack to the instance')
            ->addArgument('manifest-url', InputArgument::REQUIRED, 'The pack manifest URL');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!Validator::url()->isValid($input->getArgument('manifest-url'))) {
            $output->writeln('<error>The manifest-url must be a URL</error>');
            return Command::FAILURE;
        }

        $helper = $this->getHelper('question');

        if (!in_array(get_current_user(), ['www-data', 'nginx', 'apache'])) {
            $question = new ConfirmationQuestion("The command must run under the web server user, continue anyway? [y/N] ? ", false);

            if ($helper->ask($input, $output, $question)) {
                $output->writeln('<info>Continuing with the current user</info>');
            } else {
                $output->writeln('<error>Aborting the installation</error>');
                return Command::FAILURE;
            }
        }

        $output->writeln('<info>Downloading the manifest</info>');

        $response = await(requestURL($input->getArgument('manifest-url'), timeout: 5, headers: ['Accept: application/json']));

        if (!$response) {
            $output->writeln('<error>The manifest cannot be downloaded</error>');
            return Command::FAILURE;
        }

        $json = json_decode((string)$response->getBody());

        if ($json == null) {
            $output->writeln('<error>The manifest is not valid</error>');
            return Command::FAILURE;
        }

        $packs = array_keys(get_object_vars($json));

        $question = new ChoiceQuestion(
            'Select the pack you want to install (defaults to ' . $packs[0] . ')',
            $packs,
            0
        );
        $question->setErrorMessage('Pack %s is invalid.');
        $pack = $helper->ask($input, $output, $question);

        EmojisPack::where('name', $pack)->delete();

        $emojisPack = new EmojisPack;
        $emojisPack->name = $pack;
        $emojisPack->description = $json->{$pack}->description ?? null;
        $emojisPack->homepage = $json->{$pack}->homepage ?? null;
        $emojisPack->license = $json->{$pack}->license ?? null;
        $emojisPack->save();

        $output->writeln('<info>Downloading ' . $pack . ' - ' . $json->{$pack}->description . '</info>');

        $response = await(requestURL($json->{$pack}->src, timeout: 5));

        if (!$response) {
            $output->writeln('<error>The archive cannot be downloaded</error>');
            return Command::FAILURE;
        }

        $tempZip = tempnam(sys_get_temp_dir(), $pack);
        file_put_contents($tempZip, (string)$response->getBody());

        $output->writeln('<info>Archive downloaded, extracting...</info>');

        $zip = new ZipArchive;
        $zip->open($tempZip);

        $packPath = PUBLIC_EMOJIS_PATH . $pack;

        if (is_dir($packPath)) {
            $question = new ConfirmationQuestion('The pack seems already there, overwrite [y/N] ? ', false);

            if ($helper->ask($input, $output, $question)) {
                $output->writeln('<info>Removing the previous pack</info>');
                $this->rrmdir($packPath);
            } else {
                $output->writeln('<error>Aborting the installation</error>');
                $emojisPack->delete();
                return Command::FAILURE;
            }
        }

        mkdir($packPath);
        $zip->extractTo($packPath);

        $output->writeln('<info>Pack installed</info>');
        $output->writeln('<info>Caching the pack</info>');

        $count = 0;

        $meta = json_decode(file_get_contents($packPath . '/meta.json'));

        if (!$meta->metaVersion || $meta->metaVersion != 2) {
            $output->writeln('<error>The meta version of the package is not supported aborting</error>');
            $this->rrmdir($packPath);
            $emojisPack->delete();
            return Command::FAILURE;
        }

        foreach ($meta->emojis as $metaEmoji) {
            $emojiPath = $packPath . '/' . $metaEmoji->fileName;

            $hashed = hash(Image::$hash, file_get_contents($emojiPath));

            $image = new Image;
            $image->fromPath($emojiPath);
            $image->setKey($hashed);
            $image->save();

            $emoji = new Emoji;
            $emoji->pack = $pack;
            $emoji->name = $metaEmoji->emoji->name;
            $emoji->filename = $metaEmoji->fileName;
            $emoji->alias = $metaEmoji->emoji->aliases[0] ?? null;
            $emoji->cache_hash = $hashed;
            $emoji->cache_hash_algorythm = Image::$hash;
            $emoji->save();

            $count++;
        }

        $output->writeln('<info>' . $count . ' emojis cached</info>');

        return Command::SUCCESS;
    }

    private function rrmdir(string $directory): bool
    {
        array_map(fn (string $file) => is_dir($file) ? $this->rrmdir($file) : unlink($file), glob($directory . '/' . '*'));
        return rmdir($directory);
    }
}
