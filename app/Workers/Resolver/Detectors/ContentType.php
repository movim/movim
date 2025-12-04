<?php

namespace App\Workers\Resolver\Detectors;

use Embed\Detectors\Detector as DetectorsDetector;

class ContentType extends DetectorsDetector
{
    public function detect(): string
    {
        $contentType = $this->extractor->getResponse()->getHeader('content-type')[0];

        // Discord is having a weird .mov extension for some files, that are actually mp4
        if ($this->extractor->getUri()->getHost() == 'cdn.discordapp.com'
        && $contentType == 'video/quicktime') {
            $contentType = 'video/mp4';
        }

        return $contentType;
    }
}
