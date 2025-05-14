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
        } elseif (typeIsVideo($contentType)) {
            return 'video';
        }

        return $type;
    }
}
