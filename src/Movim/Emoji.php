<?php
/*
 * SPDX-FileCopyrightText: 2010 Jaussoin Timothée
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */
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
    private $_lastEmojiUrl = null;
    private $_lastEmojiTitle = null;

    protected function __construct()
    {
        $this->_emoji = require('Emoji/CompiledEmoji.php');
    }

    public function getEmojis()
    {
        return $this->_emoji;
    }

    public function replace(string $string, bool $noTitle = false): string
    {
        // Remove the Variation Selectors (Unicode block) for a proper comparison
        $this->_string = preg_replace('/[\x{fe00}\x{fe0f}]/u', '', $string);
        $this->_lastEmoji = null;

        $replaced = preg_replace_callback(
            '/(?:\p{Extended_Pictographic}[\p{Emoji_Modifier}\p{M}]*(?:\p{Join_Control}\p{Extended_Pictographic}[\p{Emoji_Modifier}\p{M}]*)*|\s|.)\p{M}*/u',
            function ($matches) use ($noTitle) {
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
                $this->_lastEmojiUrl = BASE_URI . 'theme/img/emojis/svg/' . $astext . '.svg';

                $dom = new \DOMDocument('1.0', 'UTF-8');
                $dom->appendChild($img = $dom->createElement('img'));
                $img->setAttribute('class', 'emoji');
                $img->setAttribute('alt', $this->_emoji[$astext]);
                if (!$noTitle) {
                    $this->_lastEmojiTitle = \emojiShortcut($this->_emoji[$astext]);
                    $img->setAttribute('title', ':' . $this->_lastEmojiTitle . ':');
                }
                $img->setAttribute('src', $this->_lastEmojiUrl);

                return $dom->saveXML($dom->documentElement);
            },
            $string
        );

        return $replaced ?? $string;
    }

    public function isSingleEmoji(): bool
    {
        return $this->_string !== null && trim($this->_string) === $this->_lastEmoji;
    }

    public function getLastSingleEmojiURL()
    {
        return $this->_lastEmojiUrl;
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

        static::$instance->_string = null;
        static::$instance->_lastEmoji = null;
        static::$instance->_lastEmojiUrl = null;
        static::$instance->_lastEmojiTitle = null;

        return static::$instance;
    }
}
