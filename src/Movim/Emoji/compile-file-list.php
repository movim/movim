<?php

$unicode = fopen('/usr/share/unicode/UnicodeData.txt', 'r');
$unicodeData = [];

while ($row = fgetcsv($unicode, separator: ";")) {
    $unicodeData[strtolower($row[0])] = strtolower($row[1]);
}

$unicode = fopen('/usr/share/unicode/emoji/emoji-zwj-sequences.txt', 'r');
$unicodeSequences = [];

while ($row = fgetcsv($unicode, separator: ";")) {
    if (count($row) > 2) {
        preg_match('/(.*)#/', $row[2], $matched);
        $unicodeSequences[str_replace(' ', '-', strtolower(trim($row[0])))] = trim($matched[1]);
    }
}

$compiled = [];

foreach (glob('../../../public/theme/img/emojis/svg/*.svg') as $file) {
    $name = pathinfo($file, PATHINFO_FILENAME);

    if (array_key_exists($name, $unicodeData)) {
        $compiled[(string)$name] = $unicodeData[$name];
    }

    if (array_key_exists($name, $unicodeSequences)) {
        $compiled[(string)$name] = $unicodeSequences[$name];
    }
}


$dump = '<?php ' . PHP_EOL . 'return [' . PHP_EOL;

foreach ($compiled as $key => $value) {
    $dump .= "    '" . $key . "' => '" . $value;
}

$dump .= '];' . PHP_EOL;

file_put_contents('CompiledEmoji.php', $dump);
