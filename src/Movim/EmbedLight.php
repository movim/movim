<?php

namespace Movim;

class EmbedLight
{
    public function __construct($embed)
    {
        $this->title            = $embed->title ? (string)$embed->title : (string)$embed->url;
        $this->description      = $embed->description;
        $this->url              = (string)$embed->url;

        $this->type = 'text';

        if (!empty($embed->getResponse()->getHeader('content-type'))) {
            $this->contentType      = $embed->getResponse()->getHeader('content-type')[0];

            if (typeIsPicture($this->contentType)) {
                $this->type = 'image';
            } elseif (typeIsVideo($this->contentType)) {
                $this->type = 'video';
            }
        }

        $this->tags             = (array)$embed->keywords;
        $this->authorName       = $embed->authorName ? (string)$embed->authorName : null;
        $this->authorUrl        = $embed->authorUrl ? (string)$embed->authorUrl : null;
        $this->providerIcon     = $embed->icon ? (string)$embed->icon : null;
        $this->providerName     = $embed->providerName;
        $this->providerUrl      = $embed->providerUrl ? (string)$embed->providerUrl : null;
        $this->publishedTime    = $embed->publishedTime;
        $this->license          = $embed->license;

        // Images
        $this->images           = [];

        if ($this->type == 'image') {
            $this->images = [
                [
                    'url' => (string)$embed->url,
                    'size' => $embed->getResponse()->getHeader('content-length')[0]
                ]
            ];
        }  elseif ($embed->image) {
            $this->images = [
                [
                    'url' => (string)$embed->image,
                    'size' => 0
                ]
            ];
        } /*elseif($embed->images) {
            foreach ($embed->images as $key => $image) {
                $this->images[$key] = [
                    'url' => (string)$key,
                    'size' => 0
                ];
            }
        }*/

        // Reset the keys
        $this->images = array_values($this->images);
        return $this;
    }
}
