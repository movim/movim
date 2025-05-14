<?php

namespace App\Workers\Resolver\Extractors\Reddit\Detectors;

use Embed\Detectors\AuthorName as Detector;

class AuthorName extends Detector
{
    public function detect(): ?string
    {
        $api = $this->extractor->getApi();

        return $api->all()[0]['data']['children'][0]['data']['author'];
    }
}
