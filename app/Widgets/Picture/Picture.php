<?php

namespace App\Widgets\Picture;

use Movim\Widget\Base;
use Movim\Image;

class Picture extends Base
{
    private $compressLimit = SMALL_PICTURE_LIMIT * 6;
    private $sizeLimit = 1920;

    public function display()
    {
        $url = html_entity_decode(urldecode($this->get('url')));
        $parsedUrl = parse_url($url);
        if (
            is_array($parsedUrl)
            && array_key_exists('host', $parsedUrl)
            && $parsedUrl['host'] == 'i.imgur.com'
        ) {
            $url = getImgurThumbnail($url);
        }

        $headers = requestHeaders($url);

        if (preg_match('/2\d{2}/', $headers['http_code'])) {
            $imported = false;
            $chunks = '';

            $max = $headers["download_content_length"] > $this->compressLimit ? $this->compressLimit : $headers["download_content_length"];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HEADER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_BUFFERSIZE, 12800);
            curl_setopt($ch, CURLOPT_NOPROGRESS, false);
            curl_setopt($ch, CURLOPT_MAXFILESIZE, $max);
            curl_setopt($ch, CURLOPT_USERAGENT, DEFAULT_HTTP_USER_AGENT);
            curl_setopt($ch, CURLOPT_WRITEFUNCTION, function ($ch, $chunk) use (&$chunks, $max) {
                $chunks .= $chunk;
                return strlen($chunk);
            });

            curl_exec($ch);
            curl_close($ch);

            $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $headers = preg_split('/[\r\n]+/', substr($chunks, 0, $headerSize));
            $body = substr($chunks, $headerSize);
            $p = null;

            $p = new Image;

            /**
             * In case of an animated GIF we get only the first frame
             */
            if (substr_count($chunks, "\x00\x21\xF9\x04") > 1) {
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
        }

        header("HTTP/1.1 301 Moved Permanently");
        header('Location: /theme/img/broken_image_filled.svg');
    }
}
