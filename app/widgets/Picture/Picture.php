<?php

use Movim\Widget\Base;
use Movim\Picture as MovimPicture;

class Picture extends Base
{
    private $compressLimit = SMALL_PICTURE_LIMIT * 4;
    private $sizeLimit = 1920;

    public function display()
    {
        $url = urldecode($this->get('url'));

        $headers = requestHeaders($url);

        if ($headers['http_code'] == 200
        && $headers["download_content_length"] <= $this->compressLimit
        && typeIsPicture($headers['content_type'])) {
            $compress = (
                $headers["download_content_length"] > SMALL_PICTURE_LIMIT * 0.5
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

            if ($compress) {
                $picture = new MovimPicture;
                $picture->fromBin($body);
                $body = $picture->set(false, 'webp', 95);

                if ($body->getImageWidth() > $this->sizeLimit || $body->getImageHeight() > $this->sizeLimit) {
                    $body->adaptiveResizeImage($this->sizeLimit, $this->sizeLimit, true, false);
                }

                header_remove('Content-Type');
                header('Content-Type: image/webp');

                $name = parse_url($url, PHP_URL_PATH);
                if (!empty($name)) {
                    header('Content-Disposition: attachment; filename="'.$name.'"');
                }
            } else {
                foreach ($headers as $header) {
                    if (strtolower(substr($header, 0, strlen('Content-Type:'))) === 'content-type:') {
                        header($header);
                    }
                }
            }

            header('Cache-Control: max-age=' . 3600*24);
            print $body;
            return;
        }

        header("HTTP/1.1 301 Moved Permanently");
        header('Location: /theme/img/empty.png');
    }
}
