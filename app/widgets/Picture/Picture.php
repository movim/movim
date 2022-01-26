<?php

use Movim\Widget\Base;
use Movim\Image;

class Picture extends Base
{
    private $compressLimit = SMALL_PICTURE_LIMIT * 10;
    private $sizeLimit = 1920;

    public function display()
    {
        $url = urldecode($this->get('url'));
        $parsedUrl = parse_url($url);

        if ($parsedUrl['host'] == 'i.imgur.com') {
            $url = getImgurThumbnail($url);
        }

        $headers = requestHeaders($url);

        if ($headers["download_content_length"] <= $this->compressLimit
        && isset($headers['content_type'])
        && typeIsPicture($headers['content_type'])) {
            $compress = (
                $headers["download_content_length"] > SMALL_PICTURE_LIMIT * 0.25
                && $headers["download_content_length"] < $this->compressLimit
            );

            $limit = $compress
                ? $this->compressLimit
                : SMALL_PICTURE_LIMIT;

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HEADER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_BUFFERSIZE, 12800);
            curl_setopt($ch, CURLOPT_NOPROGRESS, false);
            curl_setopt($ch, CURLOPT_USERAGENT, DEFAULT_HTTP_USER_AGENT);
            curl_setopt($ch, CURLOPT_PROGRESSFUNCTION, function ($downloadSize, $downloaded, $uploadSize, $uploaded) use ($limit) {
                return ($downloaded > $limit) ? 1 : 0;
            });

            $response = curl_exec($ch);
            $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            curl_close($ch);

            $headers = preg_split('/[\r\n]+/', substr($response, 0, $header_size));
            $body = substr($response, $header_size);

            $p = null;

            if ($compress && $body) {
                $p = new Image;
                $p->fromBin($body);
                $p->inMemory();
                $p->save(false, false, DEFAULT_PICTURE_FORMAT, 85);

                if ($p->getImage()->getImageWidth() > $this->sizeLimit || $p->getImage()->getImageHeight() > $this->sizeLimit) {
                    $p->getImage()->adaptiveResizeImage($this->sizeLimit, $this->sizeLimit, true, false);
                }

                header_remove('Content-Type');
                header('Content-Type: image/webp');
            } else {
                foreach ($headers as $header) {
                    if (strtolower(substr($header, 0, strlen('Content-Type:'))) === 'content-type:') {
                        header($header);
                    }
                }
            }

            if (!empty($parsedUrl['path'])) {
                header('Content-Disposition: attachment; filename="'.basename($parsedUrl['path']).'"');
            }
            header('Cache-Control: max-age=' . 3600*24);

            print $p ? $p->getImage() : $body;

            return;
        }

        header("HTTP/1.1 301 Moved Permanently");
        header('Location: /theme/img/empty.png');
    }
}
