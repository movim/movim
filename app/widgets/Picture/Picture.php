<?php

use Movim\Widget\Base;
use Movim\Picture as MovimPicture;

class Picture extends Base
{
    /**
     * Pictures from blocked hosts that needs to be proxified
     * see https://support.mozilla.org/en-US/kb/content-blocking
     */
    private $blockedHosts = ['pbs.twimg.com'];
    private $compressLimit = SMALL_PICTURE_LIMIT * 4;

    public function display()
    {
        $url = urldecode($this->get('url'));
        $uri = parse_url($url);

        // Other image websites
        if (\array_key_exists('host', $uri) && $uri['host'] == 'i.imgur.com') {
            header("HTTP/1.1 301 Moved Permanently");
            header('Location: ' . getImgurThumbnail($url));
            return;
        }

        $headers = requestHeaders($url);

        if ($headers['http_code'] == 200
        && $headers["download_content_length"] <= $this->compressLimit
        && $headers["download_content_length"] > 2000
        && typeIsPicture($headers['content_type'])) {
            $compress = (
                $headers["download_content_length"] >= SMALL_PICTURE_LIMIT
                && $headers["download_content_length"] < $this->compressLimit
            );

            // We proxify the picture if served from HTTP
            if ($uri['scheme'] === 'http'
            || $compress
            || in_array($uri['host'], $this->blockedHosts)) {
                $limit = $compress
                    ? $this->compressLimit
                    : SMALL_PICTURE_LIMIT;

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HEADER, true);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 4);
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
                    $body = $picture->set(false, DEFAULT_PICTURE_FORMAT, 85);
                    $body->adaptiveResizeImage(1920, 1920, true);

                    header('Content-Type: image/'. DEFAULT_PICTURE_FORMAT);
                } else {
                    foreach ($headers as $header) {
                        if (strtolower(substr($header, 0, strlen('Content-Type:'))) === 'content-type:') {
                            header($header);
                        }
                    }
                }

                header('Cache-Control:public, max-age=' . 3600*24);
                print $body;
                return;
            } else {
                header("HTTP/1.1 301 Moved Permanently");
                header('Location: ' . $url);
                return;
            }
        }

        header("HTTP/1.1 301 Moved Permanently");
        header('Location: /theme/img/empty.png');
    }
}
