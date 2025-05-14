<?php

namespace App\Workers\Resolver\Extractors\Reddit\Detectors;

use Embed\Detectors\ProviderName as Detector;

class ProviderName extends Detector
{
    public function detect(): string
    {
        return 'Reddit';
    }
}
