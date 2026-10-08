<?php

namespace App\Workers\Resolver\Extractors\Reddit;

use Embed\HttpApiTrait;

class Api
{
    use HttpApiTrait;

    protected function fetchData(): array
    {
        $uri = $this->extractor->getUri();
        $this->endpoint = $this->extractor->getUri()->withPath($uri->getPath() . '.json');

        return $this->fetchJSON($this->endpoint);
    }
}
