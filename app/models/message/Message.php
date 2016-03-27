<?php

namespace modl;

class Message extends Model {
    public $id;
    public $newid;

    public $session;
    public $jidto;
    public $jidfrom;

    protected $resource;

    public $type;

    protected $subject;
    protected $thread;
    protected $body;
    protected $html;

    public $published;
    public $delivered;

    public $color; // Only for chatroom purpose
    public $publishedPrepared; // Only for chat purpose
    public $edited;

    public $sticker; // The sticker code

    public function __construct()
    {
        $this->_struct = '
        {
            "session" :
                {"type":"string", "size":96, "mandatory":true },
            "id" :
                {"type":"string", "size":64},
            "jidto" :
                {"type":"string", "size":96, "mandatory":true },
            "jidfrom" :
                {"type":"string", "size":96, "mandatory":true },
            "resource" :
                {"type":"string", "size":128 },
            "type" :
                {"type":"string", "size":16, "mandatory":true },
            "subject" :
                {"type":"text"},
            "thread" :
                {"type":"string", "size":128 },
            "body" :
                {"type":"text"},
            "html" :
                {"type":"text"},
            "published" :
                {"type":"date", "mandatory":true},
            "delivered" :
                {"type":"date"},
            "edited" :
                {"type":"int", "size":1},
            "sticker" :
                {"type":"string", "size":128 }
        }';

        parent::__construct();
    }

    public function set($stanza, $parent = false)
    {
        if($stanza->body || $stanza->subject) {
            $jid = explode('/',(string)$stanza->attributes()->from);
            $to = current(explode('/',(string)$stanza->attributes()->to));

            if(isset($stanza->attributes()->id)) {
                $this->id = (string)$stanza->attributes()->id;
            }

            // This is not very beautiful
            $user = new \User;
            $this->session    = $user->getLogin();

            $this->jidto      = $to;
            $this->jidfrom    = $jid[0];

            if(isset($jid[1]))
                $this->__set('resource', $jid[1]);

            $this->type = 'chat';
            if($stanza->attributes()->type) {
                $this->type    = (string)$stanza->attributes()->type;
            }

            if($stanza->body)
                $this->__set('body', (string)$stanza->body);

            if($stanza->subject)
                $this->__set('subject', (string)$stanza->subject);

            if($stanza->html) {
                $xml = \simplexml_load_string((string)$stanza->html->body);
                if($xml) {
                    $results = $xml->xpath('//img/@src');
                    if(is_array($results) && !empty($results)) {
                        $this->sticker = getCid((string)$results[0]);
                    }
                }
            }

            if($stanza->replace) {
                $this->newid = $this->id;
                $this->id = (string)$stanza->replace->attributes()->id;
                $this->edited = true;
            }

            if($stanza->delay)
                $this->published = gmdate('Y-m-d H:i:s', strtotime($stanza->delay->attributes()->stamp));
            elseif($parent && $parent->delay)
                $this->published = gmdate('Y-m-d H:i:s', strtotime($parent->delay->attributes()->stamp));
            else
                $this->published = gmdate('Y-m-d H:i:s');
        }
    }

    public function convertEmojis()
    {
        $emoji = \MovimEmoji::getInstance();
        $this->body = addHFR($emoji->replace($this->body));
    }

    public function addUrls()
    {
        $this->body = addUrls($this->body);
    }
}
