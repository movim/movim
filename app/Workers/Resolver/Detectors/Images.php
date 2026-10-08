<?php

namespace App\Workers\Resolver\Detectors;

use Embed\Detectors\Detector as DetectorsDetector;

class Images extends DetectorsDetector
{
    public function detect(): array
    {
        $document = $this->extractor->getDocument();
        $images = [];

        foreach ($document->select('.//img')->nodes() as $node) {
            if (!empty($src = $node->getAttribute('src'))) {
                $images[] = $src;
            }
        }

        return $images;
    }
}
