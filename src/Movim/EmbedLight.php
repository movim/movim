<?php
/*
 * SPDX-FileCopyrightText: 2010 Jaussoin Timothée
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Movim;

use DateTime;

class EmbedLight
{
    public string $title;
    public ?string $description;

    public string $url;
    public string $type = 'text';
    public ?string $contentType;
    public array $tags = [];
    public array $images = [];

    public ?string $authorName;
    public ?string $authorUrl;
    public ?string $providerIcon;
    public ?string $providerName;
    public ?string $providerUrl;

    public ?string $license;
    public ?string $publishedTime;

    public function __construct($embed)
    {
        $this->title            = $embed->title ? (string)$embed->title : (string)$embed->url;
        $this->description      = $embed->description;
        $this->url              = (string)$embed->url;
        $this->type             = $embed->type;
        $this->contentType      = $embed->contentType;
        $this->tags             = (array)$embed->keywords;
        $this->authorName       = $embed->authorName ? (string)$embed->authorName : null;
        $this->authorUrl        = $embed->authorUrl ? (string)$embed->authorUrl : null;
        $this->providerIcon     = $embed->icon ? (string)$embed->icon : null;
        $this->providerName     = $embed->providerName;
        $this->providerUrl      = $embed->providerUrl ? (string)$embed->providerUrl : null;
        $this->publishedTime    = $embed->publishedTime;

        // Images
        $this->images           = [];

        if ($this->type == 'image') {
            $this->images = [
                [
                    'url' => (string)$embed->url,
                    'size' => $embed->contentLength
                ]
            ];
        }  elseif ($embed->image) {
            $this->images = [
                [
                    'url' => (string)$embed->image,
                    'size' => 0
                ]
            ];
        } elseif($embed->images) {
            foreach ($embed->images as $key => $image) {
                $this->images[$key] = [
                    'url' => (string)$key,
                    'size' => 0
                ];
            }
        }

        // Reset the keys
        $this->images = array_values($this->images);
        return $this;
    }
}
