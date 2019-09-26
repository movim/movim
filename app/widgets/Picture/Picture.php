<?php

use Movim\Widget\Base;

class Picture extends Base
{
    /**
     * Pictures from blocked hosts that needs to be proxified
     * see https://support.mozilla.org/en-US/kb/content-blocking
     */
    private $blockedHosts = ['pbs.twimg.com'];

    public function display()
    {
        $url = urldecode($this->get('url'));

        $headers = requestHeaders($url);

        if ($headers['http_code'] == 200
        && $headers["download_content_length"] <= SMALL_PICTURE_LIMIT
        && $headers["download_content_length"] > 2000
        && typeIsPicture($headers['content_type'])) {
            $components = parse_url($url);

            // We proxify the picture if served from HTTP
            if ($components['scheme'] === 'http'
            || in_array($components['host'], $this->blockedHosts)) {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HEADER, true);
                curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 4);
                curl_setopt($ch, CURLOPT_BUFFERSIZE, 12800);
                curl_setopt($ch, CURLOPT_NOPROGRESS, false);
                curl_setopt($ch, CURLOPT_PROGRESSFUNCTION, function ($downloadSize, $downloaded, $uploadSize, $uploaded) {
                    return ($downloaded > SMALL_PICTURE_LIMIT) ? 1 : 0;
                });

                $response = curl_exec($ch);
                $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
                curl_close($ch);

                $headers = preg_split('/[\r\n]+/', substr($response, 0, $header_size));
                $body = substr($response, $header_size);

                foreach ($headers as $header) {
                    if (strtolower(substr($header, 0, strlen('Content-Type:'))) === 'content-type:') {
                        header($header);
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
