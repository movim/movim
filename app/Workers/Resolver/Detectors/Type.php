<?php

namespace App\Workers\Resolver\Detectors;

use Embed\Detectors\Detector as DetectorsDetector;

class Type extends DetectorsDetector
{
    public function detect(): string
    {
        $type = 'text';
        $contentType = $this->extractor->getResponse()->getHeader('content-type')[0];

        if (typeIsPicture($contentType)) {
            return 'image';
        } elseif (
            typeIsVideo($contentType) ||
            // Discord is having a weird .mov extension for some files, that are actually mp4
            ($this->extractor->getUri()->getHost() == 'cdn.discordapp.com'
                && $contentType == 'video/quicktime')
        ) {
            return 'video';
        }

        return $type;
    }
}
