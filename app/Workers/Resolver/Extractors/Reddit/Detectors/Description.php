<?php

namespace App\Workers\Resolver\Extractors\Reddit\Detectors;

use Embed\Detectors\Description as Detector;

class Description extends Detector
{
    public function detect(): ?string
    {
        $api = $this->extractor->getApi();

        return $api->all()[0]['data']['children'][0]['data']['selftext'];
    }
}
