<?php

namespace Modl;

use \Modl\InfoDAO;
use \Modl\PresenceDAO;

use Movim\Picture;
use Movim\Session;

class Conference extends Model
{
    public $jid;
    public $conference;
    public $name;
    public $nick;
    public $autojoin;
    public $status;

    public $connected = false;

    public $_struct = [
        'jid'           => ['type' => 'string','size' => 128,'key' => true],
        'conference'    => ['type' => 'string','size' => 128,'key' => true],
        'name'          => ['type' => 'string','size' => 128,'mandatory' => true],
        'nick'          => ['type' => 'string','size' => 128],
        'autojoin'      => ['type' => 'bool'],
    ];

    public function setAvatar($vcard, $conference)
    {
        if($vcard->vCard->PHOTO->BINVAL) {
            $p = new \Movim\Picture;
            $p->fromBase((string)$vcard->vCard->PHOTO->BINVAL);
            $p->set($conference . '_muc');
        }
    }

    public function getItem()
    {
        $id = new InfoDAO;
        return $id->getJid($this->conference);
    }

    public function countConnected()
    {
        $pd = new PresenceDAO;
        return $pd->countJid($this->conference);
    }

    public function checkConnected()
    {
        $pd = new \Modl\PresenceDAO;

        if (!$this->nick) {
            $session = Session::start();
            $resource = $session->get('username');
        } else {
            $resource = $this->nick;
        }

        return ($pd->getMyPresenceRoom($this->conference) != null
             || $pd->getPresence($this->conference, $resource) != null);
    }

    public function getPhoto($size = 'l')
    {
        $sizes = [
            'm'     => [120 , false],
            's'     => [50  , false],
            'xs'    => [28  , false],
            'xxs'   => [24  , false]
        ];

        $p = new Picture;
        return $p->get($this->conference . '_muc', $sizes[$size][0], $sizes[$size][1]);
    }
}
