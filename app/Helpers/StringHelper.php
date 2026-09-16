<?php

use Cocur\Slugify\Slugify;
use Movim\Route;
use Movim\XMPPUri;

/**
 * Add missing links and links to hashtags
 */
function linkify(string $html, bool $hashtagLinks = true): string
{
    if (trim($html) === '') {
        return $html;
    }

    $dom = \Dom\HTMLDocument::createFromString(
        '<div id="movim-root">' . $html . '</div>',
        LIBXML_HTML_NOIMPLIED,
        'UTF-8'
    );

    $xpath = new \Dom\XPath($dom);
    $container = $dom->getElementById('movim-root');

    $textNodes = $xpath->query('//text()[not(ancestor::*[local-name()="a"])]', $container);

    $urlPattern = '(?<url>\b(?:https?|xmpp:(?:\/{1,3}|[a-z0-9%+#])|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}\/)(?:[^\s()<>]+|\([^\s()<>]+\))+(?:\((?:[^\s()<>]+|(?:\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:\'".,<>?\x{00AB}\x{00BB}\x{201C}\x{201D}\x{2018}\x{2019}]))';

    $hashtagPattern = '(?:(?<=[\s>])|^)#(?<tag>\w+)';

    $pattern = $hashtagLinks
        ? '/' . $urlPattern . '|' . $hashtagPattern . '/iu'
        : '/' . $urlPattern . '/iu';

    foreach (iterator_to_array($textNodes) as $node) {
        $text = $node->textContent;

        if (!preg_match($pattern, $text)) {
            continue;
        }

        preg_match_all($pattern, $text, $matches, PREG_OFFSET_CAPTURE);

        $fragment = $dom->createDocumentFragment();
        $lastPos = 0;
        $count = count($matches[0]);

        for ($i = 0; $i < $count; $i++) {
            [$full, $pos] = $matches[0][$i];

            if ($pos > $lastPos) {
                $fragment->appendChild($dom->createTextNode(substr($text, $lastPos, $pos - $lastPos)));
            }

            $isUrl = isset($matches['url'][$i]) && $matches['url'][$i][1] !== -1;

            $fragment->appendChild(
                $isUrl
                    ? buildUrlNode($dom, $matches['url'][$i][0])
                    : buildHashtagNode($dom, $matches['tag'][$i][0])
            );

            $lastPos = $pos + strlen($full);
        }

        if ($lastPos < strlen($text)) {
            $fragment->appendChild($dom->createTextNode(substr($text, $lastPos)));
        }

        $node->parentNode->replaceChild($fragment, $node);
    }

    $result = '';
    foreach ($container->childNodes as $child) {
        $result .= $dom->saveHTML($child);
    }

    return $result;
}

function buildUrlNode(\Dom\HTMLDocument $dom, string $content): \Dom\Node
{
    if (str_starts_with($content, 'xmpp:')) {
        $uri = new XMPPUri($content);
        $route = $uri->getRoute();

        if ($route) {
            $a = $dom->createElement('a');
            $a->setAttribute('href', '#');
            $a->setAttribute('onclick', "MovimUtils.reload('" . $route . "')");
            $a->textContent = $content;
            return $a;
        }

        return $dom->createTextNode($content);
    }

    if (in_array(parse_url($content, PHP_URL_SCHEME), ['http', 'https'])) {
        $a = $dom->createElement('a');
        $a->setAttribute('href', $content);
        $a->setAttribute('target', '_blank');
        $a->setAttribute('rel', 'noopener noreferrer');
        $a->textContent = $content;
        return $a;
    }

    if (preg_match('/^www\d{0,3}\./i', $content)) {
        $a = $dom->createElement('a');
        $a->setAttribute('href', 'https://' . $content);
        $a->setAttribute('target', '_blank');
        $a->setAttribute('rel', 'noopener noreferrer');
        $a->textContent = $content;
        return $a;
    }

    return $dom->createTextNode($content);
}

function buildHashtagNode(\Dom\HTMLDocument $dom, string $tag): \Dom\Node
{
    $a = $dom->createElement('a');
    $a->setAttribute('class', 'innertag');
    $a->setAttribute('href', '#');
    $a->setAttribute('onclick', "MovimUtils.reload('" . Route::urlize('tag', $tag) . "')");
    $a->textContent = '#' . $tag;
    return $a;
}

function emojiToCodePoint(string $emoji): string
{
    $emoji = mb_convert_encoding($emoji, 'UTF-32', 'UTF-8');
    $unicode = strtolower(preg_replace("/^[0]+/", "", bin2hex($emoji)));
    return $unicode;
}

function addEmojis(string $string, bool $noTitle = false): string
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
function getHashtags(string $string): array
{
    $hashtags = [];
    preg_match_all("/(^|\s)#(\w+)/u", $string, $matches);

    if ($matches) {
        $hashtags = $matches[2];
    }

    return $hashtags;
}

/**
 * @desc Echap the anti-slashs for Javascript
 */
function echapJS(string $string): string
{
    return str_replace(["\\", "'"], ["\\\\", "\\'"], $string);
}

/**
 * @desc Echap the anti-slashs for Javascript
 */
function unechap(string $string): string
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
    parse_str(str_replace(';', '&', $query), $params);
    return $params;
}

/**
 * @desc Explode JID
 */
enum JidComponent
{
    case Username;
    case Domain;
    case Bare;
    case Resource;
}

function explodeJid(string $jid, ?JidComponent $component = null): array|string|null
{
    $arr = explode('/', $jid);
    $jid = $arr[0];

    $resource = count($arr) > 1 ? implode('/', array_slice($arr, 1)) : null;
    $username = null;

    $arr = explode('@', $jid);
    $domain = $arr[0];
    if (isset($arr[1])) {
        $username = $arr[0];
        $domain = $arr[1];
    }

    if ($component != null) {
        return match($component) {
            JidComponent::Bare => $jid,
            JidComponent::Username => $username,
            JidComponent::Domain => $domain,
            JidComponent::Resource => $resource,
        };
    }

    return [
        'username' => $username,
        'domain' => $domain,
        'jid' => $jid,
        'resource' => $resource
    ];
}

/**
 * @desc Get base JID, without resource
 */
function bareJid(string $jid): string
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
            'audio/aac',
            'audio/ogg',
            'video/ogg',
            'audio/opus',
            'audio/vorbis',
            'audio/speex',
            'audio/mpeg',
            'audio/webm'
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
    if ($string == null) return 'dorange';

    // Get the Hue angle from the XEP definition
    $arr = unpack('C*', hex2bin(hash('sha1', $string)));
    $angle = (($arr[1] + $arr[2] * 256) / 65536.0) * 360;

    return hueToPalette($angle);
}

/**
 * Hue to palette
 */
function hueToPalette(float $hueAngle)
{
    $colors = array_keys(palette());
    // Pick the closest color from the palette
    $color = round($hueAngle / (360 / count($colors)));

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
