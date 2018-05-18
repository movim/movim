<?php
/*-
 * Copyright © 2018
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

class MovimEmoji
{
    protected static $instance = null;
    private $_emoji;
    private $_urlp1;
    private $_urlp2;
    private $_urlp3;
    private $_regex = array(
        /* some easy cases first */
        '/[#*0-9]\x{20E3}
         |\x{1F3F3}(?:\x{FE0F}\x{200D}\x{1F308})?
         |\x{1F3F4}(?:\x{200D}\x{2620}\x{FE0F}|\x{E0067}\x{E0062}
          (?:\x{E0065}\x{E006E}\x{E0067}|\x{E0073}\x{E0063}\x{E0074}|\x{E0077}\x{E006C}\x{E0073})\x{E007F})?
         |\x{1F441}(?:\x{200D}\x{1F5E8})?
         /ux',
        /* everything starting with 1F468 or 1F469 */
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
        /* some more combinations */
        '/[\x{261D}\x{26F7}-\x{270D}\x{1F1E6}-\x{1F1FF}\x{1F385}\x{1F3C2}-\x{1F3CC}\x{1F442}-\x{1F487}\x{1F4AA}\x{1F574}-\x{1F596}\x{1F645}-\x{1F6CC}\x{1F918}-\x{1F9DD}]
          [\x{1F1E6}-\x{1F1FF}\x{1F3FB}\x{1F3FF}]
         |[\x{26F9}\x{1F3C3}-\x{1F3CC}\x{1F46E}\x{1F46F}\x{1F461}-\x{1F477}\x{1F481}-\x{1F487}\x{1F575}\x{1F645}-\x{1F64E}\x{1F6A3}\x{1F6B4}-\x{1F6B6}\x{1F926}\x{1F937}-\x{1F93E}\x{1F9D6}-\x{1F9DF}]
          [\x{FE0F}\x{1F3FB}-\x{1F3FF}]?
          \x{200D}[\x{2640}\x{2642}]\x{FE0F}
         /ux',
        /* individual codepoints last */
        '/[\x{203C}\x{2049}\x{2139}-\x{21AA}\x{231A}-\x{23FA}\x{24C2}\x{25AA}-\x{27BF}\x{2934}-\x{2B55}\x{3030}-\x{3299}\x{1F004}-\x{1F9E6}]/u'
      );

    protected function __construct()
    {
        $this->_emoji = require('CompiledEmoji.php');
        $this->_urlp1 = '<img alt="';
        $this->_urlp2 = '" class="emoji" src="' .
          BASE_URI . 'themes/' . Configuration::findOrNew(1)->theme .
          '/img/emojis/svg/';
        $this->_urlp3 = '.svg" />';
    }

    public function replace($string)
    {
        return preg_replace_callback($this->_regex, function ($matches) {
            $astext = implode('-', array_map('dechex', unpack('N*',
              mb_convert_encoding($matches[0], 'UCS-4BE', 'UTF-8'))));
            /* do we know this character? */
            if (!isset($this->_emoji[$astext]))
                /* no, return match unchanged */
                return $matches[0];
            /* yes, replace */
            return $this->_urlp1 . $this->_emoji[$astext] . $this->_urlp2 .
              $astext . $this->_urlp3;
          }, $string);
    }

    public static function getInstance()
    {
        if (!isset(static::$instance)) {
            static::$instance = new MovimEmoji;
        }
        return static::$instance;
    }
}