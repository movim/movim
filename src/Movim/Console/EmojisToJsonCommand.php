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
            $value = str_replace([
                '+ ',
                'ZERO WIDTH JOINER ',
                'EMOJI MODIFIER FITZPATRICK',
                'MAN ',
                'WOMAN ',
                'FEMALE ',
                'MALE ',
                'EMOJI COMPONENT ',
                '  '
            ], [
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                ' '
            ], $value);
            echo $value."\n";
            $filtered[$key] = $value;
        }

        $json = [];
        foreach ($filtered as $key => $value) {
            $emojiCode = '';
            $exploded = explode('-', $key);
            foreach ($exploded as $keyElement) {
                $emojiCode .= '\u{'.$keyElement.'}';
            }

            $json[emojiShortcut($value)] = ['e' => $emojiCode, 'c' => $key];
        }

        $encoded = \json_encode($json);
        $encoded = str_replace('\\\\', '\\', $encoded);

        \file_put_contents(PUBLIC_PATH.'scripts/movim_emojis_list.js', 'var emojis = '.$encoded);

        $output->writeln('<info>'.\count($json).' emojis saved</info>');

        return 0;
    }
}
