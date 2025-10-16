<?php

use Cocur\Slugify\Slugify;
use Movim\Route;

function addUrls($string)
{
    // Add missing links
    return preg_replace_callback(
        "/<a[^>]*>[^<]*<\/a|\".*?\"|((?i)\b((?:https?|xmpp:(?:\/{1,3}|[a-z0-9%+#])|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}\/)(?:[^\s()<>]+|\([^\s()<>]+|(\([^\s()<>]+\))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:'\".,<>?«»“”‘’])))/",
        function ($match) {
            if (isset($match[1])) {
                $content = $match[1];

                $lastTag = false;
                if (in_array(substr($content, -3, 3), ['&lt', '&gt'])) {
                    $lastTag = substr($content, -3, 3);
                    $content = substr($content, 0, -3);
                }

                if (substr($content, 0, 5) == 'xmpp:') {
                    $link = str_replace(['xmpp://', 'xmpp:'], '', $content);

                    if (substr($link, -5, 5) == '?join') {
                        return stripslashes(
                            '<a href=\"' .
                                Route::urlize('chat', [str_replace('?join', '', $link), 'room']) .
                                '\">' .
                                $content .
                                '</a>'
                        ) .
                            ($lastTag !== false ? $lastTag : '');
                    }
                    return stripslashes(
                        '<a href=\"' .
                            Route::urlize('contact', $link) .
                            '\">' .
                            $content .
                            '</a>'
                    ) .
                        ($lastTag !== false ? $lastTag : '');
                }

                if (in_array(parse_url($content, PHP_URL_SCHEME), ['http', 'https'])) {
                    return stripslashes('<a href=\"' . $content . '\" target=\"_blank\" rel=\"noopener noreferrer\">' . $content . '</a>') .
                        ($lastTag !== false ? $lastTag : '');
                }

                return $content;
            }
            return $match[0];
        },
        $string
    );
}

function emojiToCodePoint(string $emoji): string
{
    $emoji = mb_convert_encoding($emoji, 'UTF-32', 'UTF-8');
    $unicode = strtolower(preg_replace("/^[0]+/", "", bin2hex($emoji)));
    return $unicode;
}

function addHashtagsLinks($string)
{
    return preg_replace_callback("/([\n\r\s>]|^)#(\w+)/u", function ($match) {
        return
            $match[1] .
            '<a class="innertag" href="#" onclick="MovimUtils.reload(\'' . \Movim\Route::urlize('tag', $match[2]) . '\')">' .
            '#' . $match[2] .
            '</a>';
    }, $string);
}

function addEmojis($string, bool $noTitle = false)
{
    $emoji = \Movim\Emoji::getInstance();
    return $emoji->replace($string, $noTitle);
}

/**
 * Slugify a string
 */
function slugify(string $string): string
{
    $slugify = new Slugify;
    return $slugify->slugify($string);
}

/**
 * @desc Prepare the string (add the a to the links and show the smileys)
 */
function prepareString($string, bool $preview = false)
{
    return addEmojis(addUrls($string, $preview));
}

/**
 * @desc Estimate the reading time of a content in minutes
 */
function readTime($content)
{
    $minutes = floor(str_word_count(strip_tags($content)) / 200);

    if ($minutes == 0) return false;

    return $minutes == 1
        ? __('post.read_time_singular', $minutes)
        : __('post.read_time_plural', $minutes);
}

/**
 * @desc Return the tags in a string
 */
function getHashtags($string): array
{
    $hashtags = [];
    preg_match_all("/(^| )#(\w+)/u", $string, $matches);

    if ($matches) {
        $hashtags = $matches[2];
    }

    return $hashtags;
}

/**
 * @desc Echap the anti-slashs for Javascript
 */
function echapJS($string): string
{
    return str_replace(["\\", "'"], ["\\\\", "\\'"], $string);
}

/**
 * @desc Echap the anti-slashs for Javascript
 */
function unechap($string): string
{
    return str_replace("\\\\", "\\", $string);
}

/**
 * @desc Extract the CID
 */
function getCid($string): ?array
{
    preg_match("/([\w\-]+)\+(\w+)\@/", $string, $matches);

    if (is_array($matches) && count($matches) > 1) {
        if (!array_key_exists($matches[1], \IANAHashToPhp())) return null;

        return ['algorythm' => \IANAHashToPhp()[$matches[1]], 'hash' => $matches[2]];
    }

    return null;
}

/**
 * @desc Explode query parameters into an array
 */
function explodeQueryParams(string $query): array
{
    $params = [];

    foreach (explode(';', $query) as $param) {
        $result = explode('=', $param);
        if (count($result) == 2) {
            $params[$result[0]] = $result[1];
        }
    }

    return $params;
}

/**
 * @desc Explode JID
 */
function explodeJid(string $jid): array
{
    $arr = explode('/', $jid);
    $jid = $arr[0];

    $resource = count($arr) > 1 ? implode('/', array_slice($arr, 1)) : null;
    $username = null;

    $arr = explode('@', $jid);
    $server = $arr[0];
    if (isset($arr[1])) {
        $username = $arr[0];
        $server = $arr[1];
    }

    return [
        'username'  => $username,
        'server'    => $server,
        'jid'       => $jid,
        'resource'  => $resource
    ];
}

/**
 * @desc Get base JID, without resource
 */
function baseJid(string $jid): string
{
    return current(explode('/', $jid));
}

/**
 * @desc Return a human readable filesize
 */
function humanSize($bytes, int $precision = 2): string
{
    $units = [
        __('filesize.byte'),
        __('filesize.kilobyte'),
        __('filesize.megabyte'),
        __('filesize.gigabyte'),
        __('filesize.terabyte')
    ];

    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log((float)$bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);

    return (string)round($bytes, $precision) . ' ' . $units[$pow];
}

/**
 * @desc Return a human readable distance in km
 */
function humanDistance(float $distance): string
{
    return ($distance < 1)
        ? __('location.less_than_one_km')
        : __('location.n_km_away', round($distance));
}

/**
 * Return a colored string in the console
 */
function colorize($string, string $color): string
{
    $colors = [
        'black'     => 30,
        'red'       => 31,
        'green'     => 32,
        'yellow'    => 33,
        'blue'      => 34,
        'purple'    => 35,
        'turquoise' => 36,
        'white'     => 37
    ];

    return "\033[" . $colors[$color] . "m" . $string . "\033[0m";
}

/**
 * @desc Check if the mimetype is a picture
 */
function typeIsPicture(string $type): bool
{
    return in_array($type, ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp']);
}

/**
 * @desc Check if the mimetype is a video
 */
function typeIsVideo(string $type): bool
{
    return in_array($type, ['video/webm', 'video/mp4']);
}

/**
 * @desc Check if the mimetype is an audio file
 */
function typeIsAudio(string $type): bool
{
    return in_array(
        $type,
        [
            'audio/aac', 'audio/ogg', 'video/ogg', 'audio/opus',
            'audio/vorbis', 'audio/speex', 'audio/mpeg', 'audio/webm'
        ]
    );
}

/**
 * @desc Check if the Provider Name is embedable
 */
function providerNameIsEmbed(string $providerName): bool
{
    return in_array($providerName, ['PeerTube', 'YouTube', 'RedGIFs']);
}

/**
 * @desc Validate a media type
 */
function isMimeType(string $mimeType): bool
{
    return preg_match('/\w+\/[-+.\w]+/', $mimeType) == 1;
}

/**
 * @desc Validate latitude
 */
function isLatitude(float $latitude): bool
{
    return $latitude > -90 && $latitude < 90;
}

/**
 * @desc Validate longitude
 */
function isLongitude(float $longitude): bool
{
    return $longitude > -180 && $longitude < 180;
}

/**
 * @desc XEP-0392: Consistent Color Generation
 */
function stringToColor(?string $string = null): string
{
    $colors = array_keys(palette());

    if ($string == null) return 'dorange';

    // Get the Hue angle from the XEP definition
    $arr = unpack('C*' ,hex2bin(hash('sha1', $string)));
    $angle = (($arr[1] + $arr[2] * 256) / 65536.0) * 360;

    // Pick the closest color from the palette
    $color = round($angle / (360 / count($colors)));

    if ($color == 16) $color = 15;

    return $colors[$color];
}

/**
 * @desc Return the base color palette
 */
function palette(bool $withBlack = false): array
{
    $palette = [
        'dorange'   => '#FF5722',
        'orange'    => '#FF9800',
        'amber'     => '#FFC107',
        'yellow'    => '#FFEB3B',
        'lime'      => '#CDDC39',
        'lgreen'    => '#8BC34A',
        'green'     => '#4CAF50',
        'teal'      => '#009688',
        'cyan'      => '#00BCD4',
        'lblue'     => '#03A9F4',
        'blue'      => '#2196F3',
        'indigo'    => '#3F51B5',
        'dpurple'   => '#673AB7',
        'purple'    => '#9C27B0',
        'pink'      => '#E91E63',
        'red'       => '#F44336',
    ];

    if ($withBlack) return $palette + [
        'black'     => '#000000',
        'gray'      => '#9E9E9E',
    ];

    return $palette;
}

/**
 * @desc Strip tags and add a whitespace
 */
function stripTags($string): string
{
    if ($string == null) return '';

    return strip_tags(
        preg_replace(
            '/\s+/',
            ' ',
            preg_replace('/(<\/[^>]+?>)(<[^>\/][^>]*?>)/', '$1 $2', $string)
        )
    );
}

/**
 * @desc To emoji shortcut
 */
function emojiShortcut($string): string
{
    return \strtolower(
        \str_replace(
            ['-', ' ', 0, 1, 2, 3, 4, 5, 6, 7, 8, 9],
            ['_', '_', 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j'],
            $string
        )
    );
}

/**
 * @desc Purify a string
 */
function purifyHTML($string, $base = null): string
{
    $config = \HTMLPurifier_Config::createDefault();
    $config->set('HTML.Doctype', 'XHTML 1.1');
    $config->set('Cache.SerializerPath', '/tmp');
    $config->set('HTML.DefinitionID', 'html5-definitions');
    $config->set('HTML.DefinitionRev', 1);

    if ($base !== null) {
        $config->set('URI.Base', $base);
        $config->set('URI.MakeAbsolute', true);
    }

    if ($def = $config->maybeGetRawHTMLDefinition()) {
        $def->addElement('video', 'Block', 'Optional: (source, Flow) | (Flow, source) | Flow', 'Common', [
            'src' => 'URI',
            'type' => 'Text',
            'width' => 'Length',
            'height' => 'Length',
            'poster' => 'URI',
            'preload' => 'Enum#auto,metadata,none',
            'controls' => 'Bool',
        ]);
        $def->addElement('audio', 'Block', 'Optional: (source, Flow) | (Flow, source) | Flow', 'Common', [
            'src' => 'URI',
            'preload' => 'Enum#auto,metadata,none',
            'muted' => 'Bool',
            'controls' => 'Bool',
        ]);
        $def->addElement('source', 'Block', 'Flow', 'Common', [
            'src' => 'URI',
            'type' => 'Text',
        ]);
    }

    $purifier = new \HTMLPurifier($config);
    $trimmed = trim($purifier->purify($string));
    return preg_replace('#(\s*<br\s*/?>)*\s*$#i', '', $trimmed);
}

/**
 * @desc Check if a string is RTL
 */
function isRTL(string $string): bool
{
    return preg_match('/\p{Arabic}|\p{Hebrew}/u', $string);
}

/**
 * @desc Invert a number
 */
function invertSign($num)
{
    return ($num <= 0) ? abs($num) : -$num;
}

/**
 * @desc Return the first two letters of a string
 */
function firstLetterCapitalize($string, bool $firstOnly = false): string
{
    $size = ($firstOnly) ? 1 : 2;
    $string = empty($string) ? 'M' : $string;
    return mb_convert_case(mb_substr($string, 0, $size), MB_CASE_TITLE);
}

/**
 * @desc Return a clean string that can be used for HTML ids
 */
function cleanupId(string $string = '', bool $withHash = false): string
{
    $id = 'id-' . strtolower(preg_replace('/([^a-z0-9]+)/i', '-', $string));
    return $withHash ? $id . '-' . substr(hash('sha256', $string), 0, 6) : $id;
}

/**
 * @desc Return a clean string that can be used for HTML ids
 */
function hashId(string $string = ''): string
{
    return 'id-' . substr(hash('sha256', $string), 0, 6);
}

/**
 * @desc Truncates the given string at the specified length.
 */
function truncate($str, int $width): string
{
    return strtok(wordwrap($str, $width, "…\n"), "\n");
}

/**
 * @desc Return the URI of a path with a timestamp
 */
function urilize($path, bool $noTime = false): string
{
    if ($noTime || !file_exists(PUBLIC_PATH . '/' . $path)) {
        return BASE_URI . $path;
    }

    return BASE_URI . $path . '?t=' . filemtime(PUBLIC_PATH . '/' . $path);
}

/**
 * @desc Return a comma-separated list of joined array elements
 */
function implodeCsv($value)
{
    return implode(', ', $value);
}
