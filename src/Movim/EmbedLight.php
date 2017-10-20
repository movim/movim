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

        return $this;
    }
}
