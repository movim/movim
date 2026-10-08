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
    protected static ?self $instance = null;

    private array $emoji;
    private ?string $string = null;
    private ?string $lastEmoji = null;
    private ?string $lastEmojiUrl = null;
    private ?string $lastEmojiTitle = null;

    protected function __construct()
    {
        $this->emoji = require('Emoji/CompiledEmoji.php');
    }

    public function getEmojis(): array
    {
        return $this->emoji;
    }

    public function replace(string $string, bool $noTitle = false): string
    {
        // Remove the Variation Selectors (Unicode block) for a proper comparison
        $this->string = preg_replace('/[\x{fe00}\x{fe0f}]/u', '', $string);
        $this->lastEmoji = null;

        $replaced = preg_replace_callback(
            '/(?:\p{Extended_Pictographic}[\p{Emoji_Modifier}\p{M}]*(?:\p{Join_Control}\p{Extended_Pictographic}[\p{Emoji_Modifier}\p{M}]*)*|\s|.)\p{M}*/u',
            function (array $matches) use ($noTitle): string {
                $astext = implode(
                    '-',
                    array_map(
                        'dechex',
                        unpack('N*', mb_convert_encoding($matches[0], 'UCS-4BE', 'UTF-8'))
                    )
                );

                if (!isset($this->emoji[$astext])) {
                    return $matches[0];
                }

                $this->lastEmoji = $matches[0];
                $this->lastEmojiUrl = BASE_URI . 'theme/img/emojis/svg/' . $astext . '.svg';

                $dom = new \DOMDocument('1.0', 'UTF-8');
                $dom->appendChild($img = $dom->createElement('img'));
                $img->setAttribute('class', 'emoji');
                $img->setAttribute('alt', $this->emoji[$astext]);
                if (!$noTitle) {
                    $this->lastEmojiTitle = \emojiShortcut($this->emoji[$astext]);
                    $img->setAttribute('title', ':' . $this->lastEmojiTitle . ':');
                }
                $img->setAttribute('src', $this->lastEmojiUrl);

                return $dom->saveXML($dom->documentElement);
            },
            $string
        );

        return $replaced ?? $string;
    }

    public function isSingleEmoji(): bool
    {
        return $this->string !== null && trim($this->string) === $this->lastEmoji;
    }

    public function getLastSingleEmojiURL(): ?string
    {
        return $this->lastEmojiUrl;
    }

    public function getLastSingleEmojiTitle(): ?string
    {
        return $this->lastEmojiTitle;
    }

    public static function getInstance()
    {
        if (!isset(static::$instance)) {
            static::$instance = new static;
        }

        static::$instance->string = null;
        static::$instance->lastEmoji = null;
        static::$instance->lastEmojiUrl = null;
        static::$instance->lastEmojiTitle = null;

        return static::$instance;
    }
}
