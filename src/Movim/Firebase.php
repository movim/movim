<?php

namespace Movim;

class Firebase
{
    private $_key;
    private $_token;

    public function __construct(string $key, string $token)
    {
        $this->_key = $key;
        $this->_token = $token;
    }

    public function notify(string $title, string $body = null, string $image = null, string $action = null)
    {
        $fields = [
            'to' => $this->_token,
            'data' => [
                'title' => $title,
                'body' => $body,
                'image' => $image,
                'action' => $action
            ]
        ];

        $this->request($fields);
    }

    public function clear(string $action)
    {
        $fields = [
            'to' => $this->_token,
            'data' => [
                'clear' => true,
                'action' => $action
            ]
        ];

        $this->request($fields);
    }

    private function request($fields)
    {
        $headers = [
            'Authorization' => 'key=' . $this->_key,
            'Content-Type' => 'application/json'
        ];

        $browser = new \React\Http\Browser;
        $browser->withTimeout(10)
            ->post(
                'https://fcm.googleapis.com/fcm/send',
                $headers,
                json_encode($fields)
            );
    }
}
