<?php

namespace Modl;

use Respect\Validation\Validator;

use Movim\Picture;
use Movim\User;

class Message extends Model
{
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
    public $displayed;

    public $markable;

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
        'displayed' => ['type' => 'date'],
        'markable'  => ['type' => 'bool'],
        'edited'    => ['type' => 'bool'],
        'picture'   => ['type' => 'text'],
        'sticker'   => ['type' => 'string','size' => 128],
        'quoted'    => ['type' => 'bool'],
        'file'      => ['type' => 'serialized']
    ];

    public function set($stanza, $parent = false)
    {
        $jid = explode('/',(string)$stanza->attributes()->from);
        $to = current(explode('/',(string)$stanza->attributes()->to));

        if((string)$stanza->attributes()->type == 'headline') return;

        // This is not very beautiful
        $user = new User;
        $this->session    = $user->getLogin();

        $this->jidto      = $to;
        $this->jidfrom    = $jid[0];

        if(isset($jid[1])) {
            $this->resource = $jid[1];
        }

        if($stanza->delay) {
            $this->published = gmdate('Y-m-d H:i:s', strtotime($stanza->delay->attributes()->stamp));
        } elseif($parent && $parent->delay) {
            $this->published = gmdate('Y-m-d H:i:s', strtotime($parent->delay->attributes()->stamp));
        } else {
            $this->published = gmdate('Y-m-d H:i:s');
        }

        if($stanza->body || $stanza->subject) {
            $this->type = 'chat';
            if($stanza->attributes()->type) {
                $this->type = (string)$stanza->attributes()->type;
            }

            if(isset($stanza->attributes()->id)
            && $this->type == 'chat') {
                $this->id = (string)$stanza->attributes()->id;
            }

            if($stanza->x
            && (string)$stanza->x->attributes()->xmlns == 'http://jabber.org/protocol/muc#user'
            && isset($jid[1])) {
                $this->jidfrom = $jid[0].'/'.$jid[1];
            }

            if($stanza->body) {
                $this->body = (string)$stanza->body;
            }

            if($stanza->markable) {
                $this->markable = true;
            } else {
                $this->markable = false;
            }

            if($stanza->subject) {
                $this->subject = (string)$stanza->subject;
            }

            if($stanza->thread) {
                $this->thread = (string)$stanza->thread;
            }

            if($this->type == 'groupchat') {
                $pd = new \Modl\PresenceDAO;
                $p = $pd->getMyPresenceRoom($this->jidfrom);

                if(is_object($p)
                && strpos($this->body, $p->resource) !== false
                && $this->resource != $p->resource) {
                    $this->quoted = true;
                }
            }

            if($stanza->html) {
                $results = [];

                $xml = \simplexml_load_string((string)$stanza->html);
                if(!$xml) {
                    $xml = \simplexml_load_string((string)$stanza->html->body);
                    if($xml) {
                        $results = $xml->xpath('//img/@src');
                    }
                } else {
                    $xml->registerXPathNamespace('xhtml', 'http://www.w3.org/1999/xhtml');
                    $results = $xml->xpath('//xhtml:img/@src');
                }

                if(!empty($results)) {
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

            if($stanza->replace) {
                $this->newid = $this->id;
                $this->id = (string)$stanza->replace->attributes()->id;
                $this->edited = true;
            }

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

            if(isset($stanza->x->invite)) {
                $this->type = 'invitation';
                $this->subject = $this->jidfrom;
                $this->jidfrom = current(explode('/',(string)$stanza->x->invite->attributes()->from));
            }

            //return $this->checkPicture();
        } elseif(isset($stanza->x)
        && $stanza->x->attributes()->xmlns == 'jabber:x:conference') {
            $this->type = 'invitation';
            $this->body = (string)$stanza->x->attributes()->reason;
            $this->subject = (string)$stanza->x->attributes()->jid;
        }
    }

    /*public function checkPicture()
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
    }*/

    public function convertEmojis()
    {
        $emoji = \MovimEmoji::getInstance();
        $this->body = addHFR($emoji->replace($this->body));
    }

    public function isTrusted()
    {
        $rd = new \Modl\RosterLinkDAO;
        $from = explode('@',(string)$this->jidfrom);
        $from = explode('.', end($from));

        $session = explode('@',(string)$this->session);

        return ($this->session == $this->jidfrom
            || end($session) == $from[count($from)-2].'.'.$from[count($from)-1]
            || $rd->get($this->jidfrom) !== null);
    }

    public function isEmpty()
    {
        return (empty($this->body)
            && empty($this->picture)
            && empty($this->sticker)
        );
    }

    public function isOTR()
    {
        return preg_match('#^\?OTR#', $this->body);
    }

    public function addUrls()
    {
        $this->body = addUrls($this->body);
    }
}
