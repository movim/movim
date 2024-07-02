<?php

namespace App\Widgets\UploadFile;

use App\Upload;
use Movim\Widget\Base;

class UploadFile extends Base
{
    public function display()
    {
        if (!$this->get('f')) {
            return;
        }

        $upload = Upload::findOrFail($this->get('f'));

        if (!$upload) return;

        if (array_key_exists('file', $_FILES)) {
            $ch = curl_init($upload->puturl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_PUT, true);
            curl_setopt($ch,CURLOPT_TIMEOUT,10);

            $tmpFile = fopen($_FILES['file']['tmp_name'], 'r');
            curl_setopt($ch, CURLOPT_INFILE, $tmpFile);
            curl_setopt($ch, CURLOPT_INFILESIZE, filesize($_FILES['file']['tmp_name']));

            if (is_array($upload->headers)) {
                $formatedHeaders = [];

                foreach ($upload->headers as $key => $value) {
                    array_push ($formatedHeaders, $key . ': ' . $value);
                }

                curl_setopt($ch, CURLOPT_HTTPHEADER, $formatedHeaders);
            }

            //curl_setopt($ch, CURLOPT_NOPROGRESS, false);
            //curl_setopt($ch, CURLOPT_BUFFERSIZE, 128);
            //curl_setopt($ch, CURLOPT_PROGRESSFUNCTION, [$this, 'progressCallback']);

            curl_exec($ch);

            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if ($httpCode >= 200 && $httpCode < 300) {
                $upload->uploaded = true;
                $upload->save();
            }

            http_response_code($httpCode);
        }
    }
}
