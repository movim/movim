<?php

namespace Moxl\Xec\Action\Bookmark;

use Moxl\Xec\Action;
use Moxl\Stanza\Bookmark;

class Set extends Action
{
    private $_arr;

    public function request()
    {
        $this->store();
        Bookmark::set($this->_arr);
    }

    public function setArr($arr)
    {
        $this->_arr = $arr;
        return $this;
    }

    public function handle($stanza, $parent = false)
    {
        \App\User::me()->session->conferences()->delete();

        foreach($this->_arr as $c) {
            if($c['type'] == 'conference') {
                $conference = new \App\Conference;

                $conference->conference     = (string)$c['jid'];
                $conference->name           = (string)$c['name'];
                $conference->nick           = (string)$c['nick'];
                $conference->autojoin       = (boolean)$c['autojoin'];

                $conference->save();
            }
        }

        $this->deliver();
    }
}
