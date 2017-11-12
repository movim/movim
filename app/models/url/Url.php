<?php

namespace Modl;

use Respect\Validation\Validator;
use Movim\EmbedLight;

class Url extends Model
{
    private $url;
    public $hash;
    public $cache;

    public $_struct = [
        'hash'  => ['type' => 'string', 'size' => 66, 'mandatory' => true, 'key' => true],
        'cache' => ['type' => 'text', 'mandatory' => true]
    ];

    public function resolve($url)
    {
        if(Validator::url()->validate($url)) {
            $this->hash = hash('sha256', $url);

            $md = new UrlDAO;
            $cached = $md->get($this->hash);

            if($cached) {
                return unserialize(base64_decode($cached->cache));
            }

            $embed = new EmbedLight(\Embed\Embed::create($url));
            $this->cache = base64_encode(serialize($embed));
            $md->set($this);
            return $embed;
        }
    }
}
