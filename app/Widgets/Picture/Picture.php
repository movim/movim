<?php

namespace App\Widgets\Picture;

use Exception;
use Movim\Image;
use Movim\Widget\Base;
use Psr\Http\Message\ResponseInterface;
use React\Http\Browser;

class Picture extends Base
{
    private $compressLimit = SMALL_PICTURE_LIMIT * 6;
    private $sizeLimit = 1920;

    public function display()
    {
        $url = str_replace(' ', '%20', html_entity_decode(urldecode($this->get('url'))));
        $parsedUrl = parse_url($url);
        if (
            is_array($parsedUrl)
            && array_key_exists('host', $parsedUrl)
            && $parsedUrl['host'] == 'i.imgur.com'
        ) {
            $url = getImgurThumbnail($url);
        }

        $browser = (new Browser())
            ->withHeader('User-Agent', DEFAULT_HTTP_USER_AGENT)
            ->withFollowRedirects(true);

        $browser->withTimeout(2)->head($url)->then(function (ResponseInterface $response) use ($url, $browser) {
            $contentLength = null;

            if ($response->hasHeader('Content-Length')) {
                $contentLength = (int)$response->getHeader('Content-Length')[0];
            }

            $max = $contentLength && $contentLength > $this->compressLimit
                ? $this->compressLimit
                : $contentLength;

            $browser->withTimeout(10)->withResponseBuffer($max)->get($url)->then(function (ResponseInterface $response) {
                $imported = false;
                $body = (string)$response->getBody();
                $p = new Image;

                // In case of an animated GIF we get only the first frame
                if (substr_count($body, "\x00\x21\xF9\x04") > 1) {
                    $body = substr($body, 0, strrpos($body, "\x00\x21\xF9\x04"));
                }

                $imported = $p->fromBin($body);

                if ($imported) {
                    $p->inMemory();
                    $p->save(quality: 85);

                    if ($p->getImage()->getImageWidth() > $this->sizeLimit || $p->getImage()->getImageHeight() > $this->sizeLimit) {
                        $p->getImage()->adaptiveResizeImage($this->sizeLimit, $this->sizeLimit, true, false);
                    }

                    header_remove('Content-Type');
                    header('Content-Type: image/' . DEFAULT_PICTURE_FORMAT);
                    header('Cache-Control: max-age=' . 3600 * 24);
                    print $p ? $p->getImage()->getImagesBlob() : $body;

                    return;
                }
            });

        }, function (Exception $e) {
            header("HTTP/1.1 301 Moved Permanently");
            header('Location: /theme/img/broken_image_filled.svg');
        });
    }
}
