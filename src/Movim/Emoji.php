<?php
/*-
 * Copyright © 2018, 2019
 *	mirabilos <thorsten.glaser@teckids.org>
 *
 * Provided that these terms and disclaimer and all copyright notices
 * are retained or reproduced in an accompanying document, permission
 * is granted to deal in this work without restriction, including un‐
 * limited rights to use, publicly perform, distribute, sell, modify,
 * merge, give away, or sublicence.
 *
 * This work is provided “AS IS” and WITHOUT WARRANTY of any kind, to
 * the utmost extent permitted by applicable law, neither express nor
 * implied; without malicious intent or gross negligence. In no event
 * may a licensor, author or contributor be held liable for indirect,
 * direct, other damage, loss, or other issues arising in any way out
 * of dealing in the work, even if advised of the possibility of such
 * damage or existence of a defect, except proven that it results out
 * of said person’s immediate fault when using the work as intended.
 */

namespace Movim;

class Emoji
{
    protected static $instance = null;
    private $_emoji;
    private $_string;
    private $_lastEmoji = null;
    private $_lastEmojiURL = null;
    private $_lastEmojiTitle = null;
    private $_regex = [
        // Some easy cases first
        '/[#*0-9]\x{20E3}
         |\x{1F3F3}(?:\x{FE0F}\x{200D}\x{1F308}|\x{FE0F}\x{200D}\x{26A7}\x{FE0F})?
         |\x{1F3F4}(?:\x{200D}\x{2620}\x{FE0F}|\x{E0067}\x{E0062}
          (?:\x{E0065}\x{E006E}\x{E0067}|\x{E0073}\x{E0063}\x{E0074}|\x{E0077}\x{E006C}\x{E0073})\x{E007F})?
         |\x{1F441}(?:\x{200D}\x{1F5E8})?
         /ux',
        // Everything starting with 1F468 or 1F469
        '/[\x{1F468}\x{1F469}]
          (?:\x{200D}\x{2764}\x{FE0F}\x{200D}(?:\x{1F48B}\x{200D})?[\x{1F468}\x{1F469}]
            |(?:\x{200D}[\x{1F468}\x{1F469}])?
             (?:\x{200D}[\x{1F466}\x{1F467}])?
             \x{200D}[\x{1F466}\x{1F467}]
            |[\x{1F3FB}-\x{1F3FF}]?\x{200D}
             (?:[\x{2695}\x{2696}\x{2708}]\x{FE0F}
               |[\x{1F33E}\x{1F373}\x{1F393}\x{1F3A4}\x{1F3A8}\x{1F3EB}\x{1F3ED}\x{1F4BB}\x{1F4BC}\x{1F527}\x{1F52C}\x{1F680}\x{1F692}]
             )
          )/ux',
        // Some more combinations (order is important!)
        '/[\x{26F9}\x{1F3C3}-\x{1F3CC}\x{1F46E}\x{1F46F}\x{1F461}-\x{1F477}\x{1F481}-\x{1F487}\x{1F575}\x{1F645}-\x{1F64E}\x{1F6A3}\x{1F6B4}-\x{1F6B6}\x{1F926}\x{1F937}-\x{1F93E}\x{1F9D6}-\x{1F9DF}\x{1F979}\x{1F9CC}]
          [\x{FE0F}\x{1F3FB}-\x{1F3FF}]?
          \x{200D}[\x{2640}\x{2642}]\x{FE0F}
         /ux',
        '/[\x{261D}\x{26F7}-\x{270D}\x{1F1E6}-\x{1F1FF}\x{1F385}\x{1F3C2}-\x{1F3CC}\x{1F442}-\x{1F487}\x{1F4AA}\x{1F574}-\x{1F596}\x{1F645}-\x{1F6CC}\x{1F918}-\x{1F9DD}\x{1F6DD}-\x{1F6DF}\x{1F7F0}]
          [\x{1F1E6}-\x{1F1FF}\x{1F3FB}-\x{1F3FF}]/ux',
        // Individual codepoints last
        '/[\x{203C}\x{2049}\x{2139}-\x{21AA}\x{231A}-\x{23FA}\x{24C2}\x{25AA}-\x{27BF}\x{2934}-\x{2B55}\x{3030}-\x{3299}\x{1F004}-\x{1F9FF}\x{1FAAA}-\x{1FAFF}]/u'
    ];

    protected function __construct()
    {
        $this->_emoji = require('Emoji/CompiledEmoji.php');
    }

    public function getEmojis()
    {
        return $this->_emoji;
    }

    public function replace($string, bool $noTitle = false): string
    {
        // Remove the Variation Selectors (Unicode block) for a proper comparison
        $this->_string = preg_replace('/[\x{fe00}\x{fe0f}]/u', '', $string);
        $this->_lastEmoji = null;

        return preg_replace_callback($this->_regex, function ($matches) use ($noTitle) {
            $astext = implode(
                '-',
                array_map(
                    'dechex',
                    unpack('N*', mb_convert_encoding($matches[0], 'UCS-4BE', 'UTF-8'))
                )
            );

            if (!isset($this->_emoji[$astext])) {
                return $matches[0];
            }

            $this->_lastEmoji = $matches[0];
            $this->_lastEmojiURL = BASE_URI . 'theme/img/emojis/svg/' . $astext . '.svg';

            $dom = new \DOMDocument('1.0', 'UTF-8');
            $dom->appendChild($img = $dom->createElement('img'));
            $img->setAttribute('class', 'emoji');
            $img->setAttribute('alt', $this->_emoji[$astext]);
            if (!$noTitle) {
                $this->_lastEmojiTitle = emojiShortcut($this->_emoji[$astext]);
                $img->setAttribute('title', ':' . $this->_lastEmojiTitle . ':');
            }
            $img->setAttribute('src', $this->_lastEmojiURL);

            return $dom->saveXML($dom->documentElement);
        }, $this->_string);
    }

    public function isSingleEmoji(): bool
    {
        return $this->_string !== null && trim($this->_string) === $this->_lastEmoji;
    }

    public function getLastSingleEmojiURL()
    {
        return $this->_lastEmojiURL;
    }

    public function getLastSingleEmojiTitle()
    {
        return $this->_lastEmojiTitle;
    }

    public static function getInstance()
    {
        if (!isset(static::$instance)) {
            static::$instance = new Emoji;
        }
        static::$instance->_emojisCount = 0;
        static::$instance->_string = null;
        static::$instance->_lastEmoji = null;
        static::$instance->_lastEmojiUrl = null;
        static::$instance->_lastEmojiTitle = null;

        return static::$instance;
    }
}
