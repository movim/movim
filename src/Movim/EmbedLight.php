<?php

namespace Movim;

class EmbedLight
{
    public function __construct($embed)
    {
        $this->title            = $embed->title;
        $this->description      = $embed->description;
        $this->url              = $embed->url;
        $this->type             = $embed->type;
        $this->contentType      = $embed->getResponse()->getContentType();
        $this->tags             = $embed->tags;
        $this->image            = $embed->image;
        $this->imageWidth       = $embed->imageWidth;
        $this->imageHeight      = $embed->imageHeight;
        $this->images           = $embed->images;
        $this->authorName       = $embed->authorName;
        $this->authorUrl        = $embed->authorUrl;
        $this->providerIcon     = $embed->providerIcon;
        $this->providerIcons    = $embed->providerIcons;
        $this->providerName     = $embed->providerName;
        $this->providerUrl      = $embed->providerUrl;
        $this->publishedTime    = $embed->publishedTime;
        $this->license          = $embed->license;

        // Adjust the default behavior of Embed by using the file size for the images size
        foreach ($embed->getDispatcher()->getAllResponses() as $response) {
            foreach ($this->images as $key => $image) {
                if ($image['url'] == $response->getUrl()) {
                    $this->images[$key]['size'] = $response->getHeader('Content-Length');
                }
            }
        }

        foreach ($this->images as $key => $image) {
            if ($key != 0 && $image['width'] < 512 && $image['height'] < 512) {
                unset($this->images[$key]);
            }
        }

        return $this;
    }
}
