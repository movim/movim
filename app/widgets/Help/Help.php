<?php

class Help extends \Movim\Widget\Base
{
    function load()
    {
    }

    function ajaxAddChatroom()
    {
        $room = 'movim@conference.movim.eu';

        $r = new Rooms;
        $r->ajaxChatroomAdd([
                'jid' => $room,
                'name'=> 'Movim Chatroom',
                'nick' => false,
                'autojoin' => 0
        ]);

        $r->ajaxJoin('movim@conference.movim.eu');

        $this->rpc('MovimUtils.redirect', $this->route('chat', [$room, 'room']));
    }

    function display()
    {
    }
}

