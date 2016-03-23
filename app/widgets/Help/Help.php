<?php

class Help extends \Movim\Widget\Base
{
    function load() 
    {
    }

    function ajaxAddChatroom()
    {
        $r = new Rooms;
        $r->ajaxChatroomAdd(
            array(
                'jid' => 'movim@conference.movim.eu',
                'name'=> 'Movim Chatroom',
                'nick' => false,
                'autojoin' => 0
            )
        );

        $r->ajaxJoin('movim@conference.movim.eu');

        RPC::call('movim_redirect', $this->route('chat'));
    }
    
    function display() 
    {
    }
}

