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
        $headers = ['Authorization:key='.$this->_key,'Content-Type:application/json'];

        $fields = [
            'to' => $this->_token,
            'data' => [
                'title' => $title,
                'body' => $body,
                'image' => $image,
                'action' => $action
            ]
        ];

        var_dump(requestURL('https://fcm.googleapis.com/fcm/send', 10, json_encode($fields), true, $headers));
    }
}