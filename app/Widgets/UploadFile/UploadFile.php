<?php

namespace App\Widgets\UploadFile;

use App\Upload;
use Movim\Widget\Base;
use Psr\Http\Message\ResponseInterface;
use React\Http\Browser;
use React\Stream\ReadableResourceStream;

class UploadFile extends Base
{
    public function display()
    {
        if (!$this->get('f')) return;

        $upload = Upload::findOrFail($this->get('f'));

        if (!$upload) return;

        $json = [
            'func' => 'message',
            'b' => [
                'c' => 'upload',
                'w' => 'Upload',
                'f' => 'ajaxHttpUploadXMPP',
                'p' => [$this->get('f')]
            ]
        ];

        requestAPI('ajax', post: [
            'sid' => SESSION_ID,
            'json' => rawurlencode(json_encode($json))
        ], await: false);

        if (array_key_exists('file', $_FILES)) {
            $browser = (new Browser())->withTimeout(10)
                ->withFollowRedirects(true);

            $filePath = $_FILES['file']['tmp_name'];
            $fileSize = filesize($filePath);
            $fileUploaded = 0;

            $file = new ReadableResourceStream(fopen($filePath, 'r'));

            $file->on('data', function ($data) use (&$fileUploaded, $fileSize, $json) {
                $fileUploaded += (strlen($data));

                $json['b']['f'] = 'ajaxHttpProgressXMPP';
                $json['b']['p'] = [floor($fileUploaded / $fileSize * 100)];

                requestAPI('ajax', post: [
                    'sid' => SESSION_ID,
                    'json' => rawurlencode(json_encode($json))
                ], await: false);
            });

            $browser->put(
                $upload->puturl,
                is_array($upload->headers) ? $upload->headers : [],
                $file
            )->then(
                function (ResponseInterface $response) use ($upload) {
                    $upload->uploaded = true;
                    $upload->save();

                    http_response_code($response->getStatusCode());
                },
                function (\Exception $e) {
                    http_response_code(406);
                }
            );
        }
    }
}
