<?php

namespace App\Widgets\UploadFile;

use App\Upload;
use Movim\Widget\Base;
use Psr\Http\Message\ResponseInterface;
use React\Http\Browser;

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
            $browser = (new Browser())->withTimeout(10)
                ->withFollowRedirects(true);

            $browser->put(
                $upload->puturl,
                is_array($upload->headers) ? $upload->headers : [],
                file_get_contents($_FILES['file']['tmp_name'])
            )->then(function (ResponseInterface $response) use ($upload) {
                $upload->uploaded = true;
                $upload->save();

                http_response_code($response->getStatusCode());
            });
        }
    }
}
