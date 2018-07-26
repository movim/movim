<?php

use Movim\Route;
use App\Configuration;

function addUrls($string, $preview = false)
{
    // Add missing links
    return preg_replace_callback("/<a[^>]*>[^<]*<\/a|\".*?\"|((?i)\b((?:[a-z][\w-]+:(?:\/{1,3}|[a-z0-9%])|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}\/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:'\".,<>?«»“”‘’])))/", function ($match) use ($preview) {
            if (isset($match[1])) {
                $content = $match[1];

                $lastTag = false;
                if (in_array(substr($content, -3, 3), ['&lt', '&gt'])) {
                    $lastTag = substr($content, -3, 3);
                    $content = substr($content, 0, -3);
                }

                if ($preview) {
                    try {
                        $embed = Embed\Embed::create($match[0]);
                        if ($embed->type == 'photo'
                        && $embed->images[0]['width'] <= 1024
                        && $embed->images[0]['height'] <= 1024) {
                            $content = '<img src="'.$match[0].'"/>';
                        } elseif ($embed->type == 'link') {
                            $content .= ' - '. $embed->title . ' - ' . $embed->providerName;
                        }
                    } catch(Exception $e) {
                        error_log($e->getMessage());
                    }
                }

                if (substr($content, 0, 5) == 'xmpp:') {
                    $link = str_replace(['xmpp://', 'xmpp:'], '', $content);

                    if (substr($link, -5, 5) == '?join') {
                        return stripslashes(
                            '<a href=\"'.
                            Route::urlize('chat', [str_replace('?join', '', $link), 'room']).
                            '\">'.
                            $content.
                            '</a>'
                        );
                    }
                    return stripslashes(
                        '<a href=\"'.
                        Route::urlize('contact', $link).
                        '\">'.
                        $content.
                        '</a>'
                    );
                }

                if (in_array(parse_url($content, PHP_URL_SCHEME), ['http', 'https'])) {
                    return stripslashes('<a href=\"'.$content.'\" target=\"_blank\" rel=\"noopener\">'.$content.'</a>').
                            ($lastTag !== false ? $lastTag : '');
                }

                return $content;
            }
            return $match[0];

        }, $string
    );

}

function addHashtagsLinks($string)
{
    return preg_replace_callback("/([\n\r\s>]|^)#(\w+)/u", function($match) {
        return
            $match[1].
            '<a class="innertag" href="'.\Movim\Route::urlize('tag', $match[2]).'">'.
            '#'.$match[2].
            '</a>';
    }, $string);
}

function addHFR($string)
{
    // HFR EasterEgg
    return preg_replace_callback(
            '/\[:([\w\s-]+)([:\d])*\]/', function ($match) {
                $num = '';
                if (count($match) == 3)
                    $num = $match[2].'/';
                return '<img class="hfr" title="'.$match[0].'" alt="'.$match[0].'" src="http://forum-images.hardware.fr/images/perso/'.$num.$match[1].'.gif"/>';
            }, $string
    );
}

function addEmojis($string)
{
    $emoji = \Movim\Emoji::getInstance();
    return $emoji->replace($string);
}

/**
 * @desc Prepare the string (add the a to the links and show the smileys)
 *
 * @param string $string
 * @param boolean display large emojis
 * @param check the links and convert them to pictures (heavy)
 * @return string
 */
function prepareString($string, $preview = false)
{
    return addEmojis(addUrls($string, $preview));
}

/**
 * @desc Return the tags in a string
 *
 * @param string $string
 * @return array
 */
function getHashtags($string)
{
    $hashtags = [];
    preg_match_all("/(#\w+)/u", $string, $matches);
    if ($matches) {
        $hashtagsArray = array_count_values($matches[0]);
        $hashtags = array_map(function($tag) {
            return substr($tag, 1);
        } ,array_keys($hashtagsArray));
    }

    return $hashtags;
}

/*
 * Echap the JID
 */
function echapJid($jid)
{
    return str_replace(' ', '\40', $jid);
}

/*
 * Echap the anti-slashs for Javascript
 */
function echapJS($string)
{
    return str_replace(["\\", "'"], ["\\\\", "\\'"], $string);
}

/*
 * Clean the resource of a jid
 */
function cleanJid($jid)
{
    $explode = explode('/', $jid);
    return reset($explode);
}

/*
 * Extract the CID
 */
function getCid($string)
{
    preg_match("/(\w+)\@/", $string, $matches);
    if (is_array($matches)) {
        return $matches[1];
    }
}

/*
 * Explose query parameters into an array
 */
function explodeQueryParams($query)
{
    $params = [];

    foreach(explode(';', $query) as $param) {
        $result = explode('=', $param);
        if (count($result) == 2) {
            $params[$result[0]] = $result[1];
        }
    }

    return $params;
}

/*
 *  Explode JID
 */
function explodeJid($jid)
{
    $arr = explode('/', $jid);
    $jid = $arr[0];

    if (isset($arr[1])) $resource = $arr[1];
    else $resource = null;

    $server = '';

    $arr = explode('@', $jid);
    $username = $arr[0];
    if (isset($arr[1])) $server = $arr[1];

    return [
        'username'  => $username,
        'server'    => $server,
        'jid'       => $jid,
        'resource'  => $resource
    ];
}

/**
 * Return a human readable filesize
 * @param string size in bytes
 * @return string
 */
function sizeToCleanSize($bytes, $precision = 2)
{
    $units = array('B', 'KB', 'MB', 'GB', 'TB');

    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);

    return round($bytes, $precision) . ' ' . $units[$pow];
}

/**
 * Return a colored string in the console
 * @param string
 * @param color
 * @return string
 */
function colorize($string, $color)
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

    return "\033[".$colors[$color]."m".$string."\033[0m";
}

/**
 * Check if the mimetype is a picture
 * @param string
 * @return bool
 */
function typeIsPicture($type)
{
    return in_array($type, ['image/jpeg', 'image/png', 'image/jpg', 'image/gif']);
}

/**
 * Check if the mimetype is an audio file
 * @param string
 * @return bool
 */
function typeIsAudio($type)
{
    return in_array($type, [
        'audio/aac', 'audio/ogg', 'video/ogg', 'audio/opus',
        'audio/vorbis', 'audio/speex', 'audio/mpeg']
    );
}

/**
 * Return a color generated from the string
 * @param string
 * @return string
 */
function stringToColor($string)
{
    $colors = [
        0 => 'red',
        1 => 'purple',
        2 => 'indigo',
        3 => 'blue',
        4 => 'green',
        5 => 'orange',
        6 => 'yellow',
        7 => 'brown',
        8 => 'lime',
        9 => 'cyan',
        10 => 'teal',
        11 => 'pink',
        12 => 'dorange',
        13 => 'lblue',
        14 => 'amber',
        15 => 'bgray',
    ];

    $s = abs(crc32($string));
    return $colors[$s%16];
}

/**
 * Strip tags and add a whitespace
 * @param string
 * @return string
 */
function stripTags($string)
{
    return strip_tags(preg_replace('/(<\/[^>]+?>)(<[^>\/][^>]*?>)/', '$1 $2', $string));
}

/**
 * Purify a string
 * @param string
 * @return string
 */
function purifyHTML($string)
{
    $config = \HTMLPurifier_Config::createDefault();
    $config->set('HTML.Doctype', 'XHTML 1.1');
    $config->set('Cache.SerializerPath', '/tmp');
    $config->set('HTML.DefinitionID', 'html5-definitions');
    $config->set('HTML.DefinitionRev', 1);
    $config->set('CSS.AllowedProperties', ['float']);
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
 * Check if a string is RTL
 * @param string
 * @return string
 */
function isRTL($string)
{
    return preg_match('/\p{Arabic}|\p{Hebrew}/u', $string);
}

/**
 * Invert a number
 * @param num
 * @return num
 */
function invertSign($num)
{
    return ($num <= 0) ? abs($num) : -$num ;
}

/**
 * Return the first two letters of a string
 * @param string
 * @return string
 */
function firstLetterCapitalize($string, $firstOnly = false)
{
    $size = ($firstOnly) ? 1 : 2;
    return mb_convert_case(mb_substr($string, 0, $size), MB_CASE_TITLE);
}

/** Return a clean string that can be used for HTML ids
 * @param string
 * @return string
 */
function cleanupId($string)
{
    return "id-" . strtolower(preg_replace('/([^a-z0-9]+)/i', '-', $string));
}

/**
 * Truncates the given string at the specified length.
 *
 * @param string $str The input string.
 * @param int $width The number of chars at which the string will be truncated.
 * @return string
 */
function truncate($str, $width)
{
    return strtok(wordwrap($str, $width, "…\n"), "\n");
}

/**
 * Return the URI of a path with a timestamp
 *
 * @param string $path
 * @return string
 */
function urilize($path, $notime = false)
{
    if ($notime) {
        return BASE_URI . $path;
    }
    return BASE_URI . $path . '?t='.filemtime(DOCUMENT_ROOT . '/'.$path);
}
