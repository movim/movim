<?php

namespace Movim\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Movim\Emoji;

class EmojisToJsonCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('emojisToJson')
            ->setDescription('Compile the supported emojis to Json');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $filtered = [];
        $emojis = (Emoji::getInstance())->getEmojis();

        foreach ($emojis as $key => $value) {
            if  (strpos($key, '-') === false) {
                $filtered[$key] = $value;
            }
        }

        $json = [];
        foreach ($filtered as $key => $value) {
            sscanf('U+'.$key, 'U+%x', $codepoint);
            $json[emojiShortcut($value)] = ['e' => \IntlChar::chr($codepoint), 'c' => $key];
        }

        \file_put_contents(PUBLIC_PATH.'scripts/movim_emojis_list.js', 'var emojis = '.\json_encode($json));

        $output->writeln('<info>'.\count($json).' emojis saved</info>');
    }
}
