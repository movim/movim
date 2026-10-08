<?php

namespace App\Workers\Resolver\Extractors\Reddit\Detectors;

use Embed\Detectors\Image as Detector;
use Psr\Http\Message\UriInterface;
use React\Http\Message\Uri;

class AuthorUrl extends Detector
{
    public function detect(): ?UriInterface
    {
        $api = $this->extractor->getApi();

        return new Uri('https://reddit.com/u/' . $api->all()[0]['data']['children'][0]['data']['author']);
    }
}
