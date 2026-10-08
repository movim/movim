<?php

$emojis = file_get_contents('https://unicode.org/Public/emoji/latest/emoji-test.txt');

preg_match_all('/([A-Z0-9 ]+).*E[\d\.]+\s(.*)/', $emojis, $matched);

$unicodeData = [];

foreach ($matched[1] as $key => $row) {
    $unicodeData[strtolower(str_replace(' ', '-', trim($row)))] = str_replace(':', '', strtolower($matched[2][$key]));
}

$compiled = [];

foreach (glob('../../../public/theme/img/emojis/svg/*.svg') as $file) {
    $name = pathinfo($file, PATHINFO_FILENAME);

    if (array_key_exists($name, $unicodeData)) {
        $compiled[(string)$name] = $unicodeData[$name];
    }
}

$dump = '<?php ' . PHP_EOL . 'return [' . PHP_EOL;

foreach ($compiled as $key => $value) {
    $dump .= "    '" . $key . "' => '" . $value . "',". PHP_EOL;
}

$dump .= '];' . PHP_EOL;

file_put_contents('CompiledEmoji.php', $dump);
