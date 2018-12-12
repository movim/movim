<?php

use Respect\Validation\Validator;
use Movim\Picture as PictureManager;

class Picture extends \Movim\Widget\Base
{
    function display()
    {
        $url = urldecode($this->get('url'));

        if (Validator::url()->validate($url)) {
            $headers = requestHeaders($url);

            if ($headers['http_code'] == 200
            && $headers["download_content_length"] <= SMALL_PICTURE_LIMIT
            && $headers["download_content_length"] > 2000
            && typeIsPicture($headers['content_type'])) {
                $components = parse_url($url);

                // We proxify the picture if served from HTTP
                if ($components['scheme'] === 'http') {
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_HEADER, true);
                    curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 4);
                    curl_setopt($ch, CURLOPT_BUFFERSIZE, 12800);
                    curl_setopt($ch, CURLOPT_NOPROGRESS, false);
                    curl_setopt($ch, CURLOPT_PROGRESSFUNCTION, function($downloadSize, $downloaded, $uploadSize, $uploaded) {
                        return ($downloaded > SMALL_PICTURE_LIMIT) ? 1 : 0;
                    });

                    list($header, $contents) = preg_split('/([\r\n][\r\n])\\1/', curl_exec($ch), 2);
                    curl_close ($ch);

                    $headers = preg_split('/[\r\n]+/', $header);

                    foreach ($headers as $header) {
                        if (preg_match('/^(?:Content-Type|Content-Language|Set-Cookie):/i', $header)) {
                            header($header);
                        }
                    }

                    header('Cache-Control:public, max-age=' . 3600*24);
                    print $contents;
                    return;
                } else {
                    header("HTTP/1.1 301 Moved Permanently");
                    header('Location: ' . $url);
                    return;
                }
            }

            header("HTTP/1.1 301 Moved Permanently");
            header('Location: ' . $this->respath('empty.png'));
        }
    }
}
