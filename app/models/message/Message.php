<?php

namespace modl;

class Message extends Model {
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

    public function __construct()
    {
        $this->_struct = '
        {
            "session" :
                {"type":"string", "size":128, "mandatory":true },
            "jidto" :
                {"type":"string", "size":128, "mandatory":true },
            "jidfrom" :
                {"type":"string", "size":128, "mandatory":true },
            "resource" :
                {"type":"string", "size":128 },
            "type" :
                {"type":"string", "size":20 },
            "subject" :
                {"type":"text"},
            "thread" :
                {"type":"string", "size":128 },
            "body" :
                {"type":"text"},
            "html" :
                {"type":"text"},
            "published" :
                {"type":"date"},
            "delivered" :
                {"type":"date"}
        }';

        parent::__construct();
    }

    public function set($stanza, $parent = false)
    {
        if($stanza->body || $stanza->subject) {
            $jid = explode('/',(string)$stanza->attributes()->from);
            $to = current(explode('/',(string)$stanza->attributes()->to));

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

            $this->__set('body', (string)$stanza->body);
            $this->__set('subject', (string)$stanza->subject);

            $images = (bool)($this->type == 'chat');

            /*if($stanza->html) {
                $this->html = \cleanHTMLTags($stanza->html->body->asXML());
                $this->html = \fixSelfClosing($this->html);
                $this->html = \prepareString($this->html, false, $images);
            } else {*/
            //    $this->html = \prepareString($this->body, false, $images);
            //}

            if($stanza->delay)
                $this->published = gmdate('Y-m-d H:i:s', strtotime($stanza->delay->attributes()->stamp));
            elseif($parent && $parent->delay)
                $this->published = gmdate('Y-m-d H:i:s', strtotime($parent->delay->attributes()->stamp));
            else
                $this->published = gmdate('Y-m-d H:i:s');
            $this->delivered = gmdate('Y-m-d H:i:s');
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
