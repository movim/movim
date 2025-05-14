<?php

namespace App\Workers\Resolver\Detectors;

use Embed\Detectors\Detector as DetectorsDetector;

class ContentLength extends DetectorsDetector
{
    public function detect(): ?string
    {
        $contentLength = $this->extractor->getResponse()->getHeader('content-length');

        return $contentLength ? $contentLength[0] : null;
    }
}
