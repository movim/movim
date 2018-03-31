<?php

namespace App;

use Ramsey\Uuid\Uuid;
use CoenJacobs\EloquentCompositePrimaryKeys\HasCompositePrimaryKey;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasCompositePrimaryKey;

    protected $primaryKey = ['user_id', 'id'];
    public $incrementing = false;

    protected $guarded = [];

    protected $attributes = [
        'type'    => 'chat'
    ];

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    /*public function getFileAttribute()
    {
        return unserialize($this->attributes['file']);
    }

    public function setFileAttribute($file)
    {
        $this->attributes['file'] = serialize($file);
    }*/

    public static function findByStanza($stanza)
    {
        $id = ($stanza->replace)
            ? (string)$stanza->replace->attributes()->id
            : (string)$stanza->attributes()->id;

        if (!empty($id)) {
            return self::firstOrNew([
                'user_id' => \App\User::me()->id,
                'id' => $id
            ]);
        }

        return new Message;
    }

    public function set($stanza, $parent = false)
    {
        if (!isset($this->id)) {
            $this->id = 'm_'.(string)Uuid::uuid4();
        }

        $jid = explode('/',(string)$stanza->attributes()->from);
        $to = current(explode('/',(string)$stanza->attributes()->to));

        $this->user_id    = \App\User::me()->id;
        $this->jidto      = $to;
        $this->jidfrom    = $jid[0];

        if (isset($jid[1])) {
            $this->resource = $jid[1];
        }

        if ($stanza->delay) {
            $this->published = gmdate('Y-m-d H:i:s', strtotime($stanza->delay->attributes()->stamp));
        } elseif ($parent && $parent->delay) {
            $this->published = gmdate('Y-m-d H:i:s', strtotime($parent->delay->attributes()->stamp));
        } else {
            $this->published = gmdate('Y-m-d H:i:s');
        }

        if ($stanza->body || $stanza->subject) {
            $this->type = 'chat';
            if ($stanza->attributes()->type) {
                $this->type = (string)$stanza->attributes()->type;
            }

            if (isset($stanza->attributes()->id)) {
                $this->id = (string)$stanza->attributes()->id;
            }

            if ($stanza->x
            && (string)$stanza->x->attributes()->xmlns == 'http://jabber.org/protocol/muc#user'
            && isset($jid[1])) {
                $this->jidfrom = $jid[0].'/'.$jid[1];
            }

            if ($stanza->body) {
                $this->body = (string)$stanza->body;
            }

            # HipChat MUC specific cards
            if (in_array(
                explodeJid($this->jidfrom)['server'],
                ['conf.hipchat.com', 'conf.btf.hipchat.com']
            )
            && $this->type == 'groupchat'
            && $stanza->x
            && $stanza->x->attributes()->xmlns == 'http://hipchat.com/protocol/muc#room'
            && $stanza->x->card) {
                $this->body = trim(html_entity_decode($this->body));
            }

            $this->markable = (bool)($stanza->markable);

            if ($stanza->subject) {
                $this->subject = (string)$stanza->subject;
            }

            if ($stanza->thread) {
                $this->thread = (string)$stanza->thread;
            }

            if ($this->type == 'groupchat') {
                $pd = new \Modl\PresenceDAO;
                $p = $pd->getMyPresenceRoom($this->jidfrom);

                if (is_object($p)
                && strpos($this->body, $p->resource) !== false
                && $this->resource != $p->resource) {
                    $this->quoted = true;
                }
            }

            if ($stanza->html) {
                $results = [];

                $xml = \simplexml_load_string((string)$stanza->html);
                if (!$xml) {
                    $xml = \simplexml_load_string((string)$stanza->html->body);
                    if ($xml) {
                        $results = $xml->xpath('//img/@src');
                    }
                } else {
                    $xml->registerXPathNamespace('xhtml', 'http://www.w3.org/1999/xhtml');
                    $results = $xml->xpath('//xhtml:img/@src');
                }

                if (!empty($results)) {
                    if (substr((string)$results[0], 0, 10) == 'data:image') {
                        $str = explode('base64,', $results[0]);
                        if (isset($str[1])) {
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

            if ($stanza->reference) {
                $filetmp = [];

                if ($stanza->reference->{'media-sharing'}) {
                    $file = $stanza->reference->{'media-sharing'}->file;
                    if (isset($file)) {
                        if (preg_match('/\w+\/[-+.\w]+/', $file->{'media-type'}) == 1) {
                            $filetmp['type'] = (string)$file->{'media-type'};
                        }
                        $filetmp['size'] = (int)$file->size;
                        $filetmp['name'] = (string)$file->name;
                    }

                    if ($stanza->reference->{'media-sharing'}->sources) {
                        $source = $stanza->reference->{'media-sharing'}->sources->reference;

                        if (!filter_var((string)$source->attributes()->uri, FILTER_VALIDATE_URL) === false) {
                            $filetmp['uri'] = (string)$source->attributes()->uri;
                        }
                    }

                    if (array_key_exists('uri', $filetmp)
                    && array_key_exists('type', $filetmp)
                    && array_key_exists('size', $filetmp)
                    && array_key_exists('name', $filetmp)) {
                        $this->file = $filetmp;
                    }
                } elseif ($stanza->reference->attributes()->type == 'mention'
                    && parse_url($stanza->reference->attributes()->uri !== false)) {
                    $begin = '<a href="' . Route::urlize('share', $stanza->reference->attributes()->uri) . '">';

                    $this->html = substr_replace(
                        $this->body,
                        $begin,
                        (int)$stanza->reference->attributes()->begin,
                        0
                    );
                    $this->html = substr_replace(
                        $this->html,
                        '</a>',
                        (int)$stanza->reference->attributes()->end + strlen($begin),
                        0
                    );
                }
            }

            if ($stanza->replace
            && $this->user->messages()->where('id', $this->id)->count() == 0) {
                $this->oldid = (string)$stanza->replace->attributes()->id;
                $this->edited = true;
                Message::where('id', (string)$stanza->replace->attributes()->id)->update([
                    'id' => $this->id,
                    'edited' => true
                ]);
            }

            if (isset($stanza->x->invite)) {
                $this->type = 'invitation';
                $this->subject = $this->jidfrom;
                $this->jidfrom = current(explode('/',(string)$stanza->x->invite->attributes()->from));
            }

            //return $this->checkPicture();
        } elseif (isset($stanza->x)
        && $stanza->x->attributes()->xmlns == 'jabber:x:conference') {
            $this->type = 'invitation';
            $this->body = (string)$stanza->x->attributes()->reason;
            $this->subject = (string)$stanza->x->attributes()->jid;
        }
    }

    public function convertEmojis()
    {
        $emoji = \MovimEmoji::getInstance();
        $this->body = addHFR($emoji->replace($this->body));
    }

    public function isTrusted()
    {
        /*$rd = new \Modl\RosterLinkDAO;
        $from = explode('@', cleanJid((string)$this->jidfrom));
        $from = explode('.', end($from));

        $session = explode('@',(string)$this->session);

        return ($this->session == $this->jidfrom
            || end($session) == $from[count($from)-2].'.'.$from[count($from)-1]
            || $rd->get($this->jidfrom) !== null);*/
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
