<?php

namespace App\Workers\Resolver\Detectors;

use Embed\Detectors\Detector as DetectorsDetector;

class ContentType extends DetectorsDetector
{
    public function detect(): string
    {
        return $this->extractor->getResponse()->getHeader('content-type')[0];
    }
}
