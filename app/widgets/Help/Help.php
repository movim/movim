<?php

class Help extends \Movim\Widget\Base
{
    function load()
    {
    }

    function ajaxAddChatroom()
    {
        $r = new Rooms;
        $r->ajaxChatroomAdd([
                'jid' => 'movim@conference.movim.eu',
                'name'=> 'Movim Chatroom',
                'nick' => false,
                'autojoin' => 0
        ]);

        $r->ajaxJoin('movim@conference.movim.eu');

        $this->rpc('MovimUtils.redirect', $this->route('chat'));
    }

    function display()
    {
    }
}

