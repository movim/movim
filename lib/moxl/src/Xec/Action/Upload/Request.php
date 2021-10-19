<?php

namespace Moxl\Xec\Action\Upload;

use Moxl\Xec\Action;
use Moxl\Stanza\Upload;

class Request extends Action
{
    protected $_to;
    protected $_name;
    protected $_size;
    protected $_type;

    public function request()
    {
        $this->store();
        Upload::request($this->_to, $this->_name, $this->_size, $this->_type);
    }

    public function handle($stanza, $parent = false)
    {
        if ($stanza->slot) {
            $params = [
                'get' => (string)$stanza->slot->get->attributes()->url,
                'put' => (string)$stanza->slot->put->attributes()->url,
                'headers' => null
            ];

            if ($stanza->slot->put->header) {
                $headers = [];

                foreach($stanza->slot->put->header as $header) {
                    $headers[(string)$header->attributes()->name] = (string)$header;
                }

                $params['headers'] = $headers;
            }

            $this->pack($params);
            $this->deliver();
        }
    }

    public function error($error)
    {
        $this->pack($this->_to);
        $this->deliver();
    }

    // the file size was too large
    public function errorFileTooLarge($error)
    {
        $this->pack($this->_to);
        $this->deliver();
    }

    // the client exceeded a quota
    public function errorResourceConstraint($error)
    {
        $this->pack($this->_to);
        $this->deliver();
    }

    // the client is not allowed to upload files
    public function errorNotAllowed($error)
    {
        $this->pack($this->_to);
        $this->deliver();
    }
}
