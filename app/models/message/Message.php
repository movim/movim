<?php

namespace Modl;

use Respect\Validation\Validator;

use Movim\Picture;

class Message extends Model {
    public $id;
    public $newid;

    public $session;
    public $jidto;
    public $jidfrom;

    public $resource;

    public $type;

    public $subject;
    public $thread;
    public $body;
    public $html;

    public $published;
    public $delivered;

    public $color; // Only for chatroom purpose
    public $publishedPrepared; // Only for chat purpose
    public $edited;

    public $picture; // A valid (small) picture URL
    public $sticker; // The sticker code
    public $quoted;  // If the user was quoted in the message

    public $file;

    public $rtl = false;

    public $_struct = [
        'session'   => ['type' => 'string','size' => 96,'mandatory' => true],
        'id'        => ['type' => 'string','size' => 64],
        'jidto'     => ['type' => 'string','size' => 96,'mandatory' => true],
        'jidfrom'   => ['type' => 'string','size' => 96,'mandatory' => true],
        'resource'  => ['type' => 'string','size' => 128],
        'type'      => ['type' => 'string','size' => 16,'mandatory' => true],
        'subject'   => ['type' => 'text'],
        'thread'    => ['type' => 'string','size' => 128],
        'body'      => ['type' => 'text'],'html' => ['type' => 'text'],
        'published' => ['type' => 'date','mandatory' => true],
        'delivered' => ['type' => 'date'],
        'edited'    => ['type' => 'int','size' => 1],
        'picture'   => ['type' => 'text'],
        'sticker'   => ['type' => 'string','size' => 128],
        'quoted'    => ['type' => 'int','size' => 1],
        'file'      => ['type' => 'serialized']
    ];

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
                $this->resource = $jid[1];

            $this->type = 'chat';
            if($stanza->attributes()->type) {
                $this->type = (string)$stanza->attributes()->type;
            }

            if($stanza->body)
                $this->body = (string)$stanza->body;

            if($stanza->subject)
                $this->subject = (string)$stanza->subject;

            if($this->type == 'groupchat') {
                $pd = new \Modl\PresenceDAO;
                $p = $pd->getMyPresenceRoom($this->jidfrom);

                if(is_object($p)
                && strpos($this->body, $p->resource) !== false) {
                    $this->quoted = true;
                }
            }

            if($stanza->html) {
                $xml = \simplexml_load_string((string)$stanza->html->body);
                if($xml) {
                    $results = $xml->xpath('//img/@src');
                    if(is_array($results) && !empty($results)) {
                        if(substr((string)$results[0], 0, 10) == 'data:image') {
                            $str = explode('base64,', $results[0]);
                            if(isset($str[1])) {
                                $p = new Picture;
                                $p->fromBase(urldecode($str[1]));
                                $key = sha1(urldecode($str[1]));
                                $p->set($key, 'png');

                                $this->sticker = $key;
                            }
                        } else {
                            $this->sticker = getCid((string)$results[0]);
                        }
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

            if($stanza->reference) {
                $filetmp = [];

                $file = $stanza->reference->{'media-sharing'}->file;
                if(isset($file)) {
                    if(preg_match('/\w+\/[-+.\w]+/', $file->{'media-type'}) == 1) {
                        $filetmp['type'] = (string)$file->{'media-type'};
                    }
                    $filetmp['size'] = (int)$file->size;
                    $filetmp['name'] = (string)$file->name;
                }

                if($stanza->reference->{'media-sharing'}->sources) {
                    $source = $stanza->reference->{'media-sharing'}->sources->reference;

                    if(!filter_var((string)$source->attributes()->uri, FILTER_VALIDATE_URL) === false) {
                        $filetmp['uri'] = (string)$source->attributes()->uri;
                    }
                }

                if(array_key_exists('uri', $filetmp)
                && array_key_exists('type', $filetmp)
                && array_key_exists('size', $filetmp)
                && array_key_exists('name', $filetmp)) {
                    $this->file = $filetmp;
                }
            }

            return $this->checkPicture();
        }
    }

    public function checkPicture()
    {
        $body = trim($this->body);

        if(Validator::url()->notEmpty()->validate($body)) {
            $check = new \Movim\Task\CheckSmallPicture;
            return $check->run($body)
                ->then(function($small) use($body) {
                    if($small) $this->picture = $body;
                });
        }

        return new \React\Promise\Promise(function($resolve) {
            $resolve(true);
        });
    }

    public function convertEmojis()
    {
        $emoji = \MovimEmoji::getInstance();
        $this->body = addHFR($emoji->replace($this->body));
    }

    public function isTrusted()
    {
        $rd = new \Modl\RosterLinkDAO;

        return ($this->session == $this->jidfrom
            || $rd->get($this->jidfrom) !== null);
    }

    public function addUrls()
    {
        $this->body = addUrls($this->body);
    }
}
