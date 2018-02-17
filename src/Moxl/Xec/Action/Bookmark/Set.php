<?php

namespace Moxl\Xec\Action\Bookmark;

use Moxl\Xec\Action;
use Moxl\Stanza\Bookmark;

class Set extends Action
{
    private $_to;
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

    public function setTo($to)
    {
        $this->_to = $to;
        return $this;
    }

    public function handle($stanza, $parent = false)
    {
        $cd = new \Modl\ConferenceDAO;

        // We clear the old Bookmarks
        $cd->delete();

        // We save the bookmarks as Subscriptions in the database
        foreach($this->_arr as $c) {
            if($c['type'] == 'conference') {
                $co = new \Modl\Conference;

                $co->jid            = $this->_to;
                $co->conference     = (string)$c['jid'];
                $co->name           = (string)$c['name'];
                $co->nick           = (string)$c['nick'];
                $co->autojoin       = (int)$c['autojoin'];
                $co->status         = 0;

                $cd->set($co);
            }
        }

        $this->deliver();
    }
}
