<?php

use Respect\Validation\Validator;

class Picture extends \Movim\Widget\Base
{
    function load()
    {
    }

    function display()
    {
        $url = urldecode($this->get('url'));

        if(Validator::url()->validate($url)) {
            $headers = requestHeaders($url);

            if($headers['http_code'] == 200
            && $headers["download_content_length"] <= SMALL_PICTURE_LIMIT
            && $headers["download_content_length"] > 100
            && typeIsPicture($headers['content_type'])) {
                header("HTTP/1.1 301 Moved Permanently");
                header('Location: '.$url);
            }
        }
    }
}
