<?php

namespace App\Workers\Resolver\Extractors\Reddit\Detectors;

use Embed\Detectors\Image as Detector;
use Psr\Http\Message\UriInterface;
use React\Http\Message\Uri;

class Image extends Detector
{
    public function detect(): ?UriInterface
    {
        $api = $this->extractor->getApi();

        $data = $api->all()[0]['data']['children'][0]['data'];
        $imageUrl = null;

        if (array_key_exists('media_metadata', $data)) {
            $medias = $data['media_metadata'];

            if (!empty($medias)) {
                $medias = array_reverse($medias);
                $media = array_pop($medias);
                $key = substr((string)$media['m'], 0, 6);
                if ($key == 'image/') {
                    $imageUrl = new Uri('https://i.redd.it/' . $media['id'] . '.' . substr((string)$media['m'], 6));
                }
            }
        } elseif (array_key_exists('post_hint', $data) && $data['post_hint'] == 'image') {
            $imageUrl = new Uri($data['url']);
        }

        return $imageUrl;
    }
}
