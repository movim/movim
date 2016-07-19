<?php

use HeyUpdate\Emoji\Emoji;
use HeyUpdate\Emoji\EmojiIndex;

/**
 * @desc A singleton wrapper for the Emoji library
 */
class MovimEmoji
{
    protected static $instance = null;
    private $_emoji;
    private $_theme;

    protected function __construct()
    {
        $cd = new \Modl\ConfigDAO();
        $config = $cd->get();
        $this->_theme = $config->theme;

        $this->_emoji = new Emoji(new EmojiIndex(), $this->getPath());
    }

    public function replace($string)
    {
        $this->_emoji->setAssetUrlFormat($this->getPath());
        $string = $this->_emoji->replaceEmojiWithImages($string);
        $this->_emoji->setAssetUrlFormat($this->getPath());

        return $string;
    }

    private function getPath()
    {
        return BASE_URI . 'themes/' . $this->_theme . '/img/emojis/svg/%s.svg';
    }

    public static function getInstance()
    {
        if (!isset(static::$instance)) {
            static::$instance = new MovimEmoji;
        }
        return static::$instance;
    }
}

function addUrls($string, $preview = false) {
    // Add missing links
    return preg_replace_callback(
        "/([\w\"'>]+\:\/\/[\w-?'&;!#+,%:~=\.\/\@\(\)]+)/u", function ($match) use($preview) {
            if(!in_array(substr($match[0], 0, 1), array('>', '"', '\''))) {
                $content = $match[0];

                if($preview) {
                    try {
                        $embed = Embed\Embed::create($match[0]);
                        if($embed->type == 'photo'
                        && $embed->images[0]['width'] <= 1024
                        && $embed->images[0]['height'] <= 1024) {
                            $content = '<img src="'.$match[0].'"/>';
                        } elseif($embed->type == 'link') {
                            $content .= ' - '. $embed->title . ' - ' . $embed->providerName;
                        }
                    } catch(Exception $e) {
                        error_log($e->getMessage());
                    }
                }

                return stripslashes('<a href=\"'.$match[0].'\" target=\"_blank\">'.$content.'</a>');
            } else {
                return $match[0];
            }

        }, $string
    );
}

function addHFR($string) {
    // HFR EasterEgg
    return preg_replace_callback(
            '/\[:([\w\s-]+)([:\d])*\]/', function ($match) {
                $num = '';
                if(count($match) == 3)
                    $num = $match[2].'/';
                return '<img class="hfr" title="'.$match[0].'" alt="'.$match[0].'" src="http://forum-images.hardware.fr/images/perso/'.$num.$match[1].'.gif"/>';
            }, $string
    );
}

/**
 * @desc Prepare the string (add the a to the links and show the smileys)
 *
 * @param string $string
 * @param boolean display large emojis
 * @param check the links and convert them to pictures (heavy)
 * @return string
 */
function prepareString($string, $large = false, $preview = false) {
    $string = addUrls($string, $preview);

    // We add some smileys...
    return trim((string)requestURL('http://localhost:1560/emojis/', 2, ['string' => $string]));
}

/**
 * Return an array of informations from a XMPP uri
 */
function explodeURI($uri) {
    $arr = parse_url(urldecode($uri));
    $result = [];

    if(isset($arr['query'])) {
        $query = explode(';', $arr['query']);


        foreach($query as $elt) {
            if($elt != '') {
                list($key, $val) = explode('=', $elt);
                $result[$key] = $val;
            }
        }

        $arr = array_merge($arr, $result);
    }

    return $arr;

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
    return str_replace("\\", "\\\\", $string);
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
    if(is_array($matches)) {
        return $matches[1];
    }
}

/*
 *  Explode JID
 */
function explodeJid($jid)
{
    $arr = explode('/', $jid);
    $jid = $arr[0];

    if(isset($arr[1])) $resource = $arr[1];
    else $resource = null;

    $server = '';

    $arr = explode('@', $jid);
    $username = $arr[0];
    if(isset($arr[1])) $server = $arr[1];

    return array(
        'username'  => $username,
        'server'    => $server,
        'resource' => $resource
        );
}

/**
 * Return a human readable filesize
 * @param string size in bytes
 * @return string
 */
function sizeToCleanSize($size)
{
    $units = array( 'B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
    $power = $size > 0 ? floor(log($size, 1024)) : 0;
    return number_format($size / pow(1024, $power), 7, '.', ',') . ' ' . $units[$power];
}

/**
 * Return a colored string in the console
 * @param string
 * @param color
 * @return string
 */
function colorize($string, $color) {
    $colors = array(
        'black'     => 30,
        'red'       => 31,
        'green'     => 32,
        'yellow'    => 33,
        'blue'      => 34,
        'purple'    => 35,
        'turquoise' => 36,
        'white'     => 37
    );

    return "\033[".$colors[$color]."m".$string."\033[0m";
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
        7 => 'brown'
    ];

    $s = crc32($string);
    return $colors[$s%8];
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
    return (string)requestURL('http://localhost:1560/purify/', 2, ['html' => urlencode($string)]);
}

/**
 * Return the first two letters of a string
 * @param string
 * @return string
 */
function firstLetterCapitalize($string) {
    return ucfirst(strtolower(mb_substr($string, 0, 2)));
}

/**
 * Truncates the given string at the specified length.
 *
 * @param string $str The input string.
 * @param int $width The number of chars at which the string will be truncated.
 * @return string
 */
function truncate($str, $width) {
    return strtok(wordwrap($str, $width, "…\n"), "\n");
}
