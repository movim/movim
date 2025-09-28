<?php

namespace App\Widgets\Blocked;

use App\Widgets\Toast\Toast;
use Moxl\Xec\Action\Blocking\Unblock;
use Moxl\Xec\Payload\Packet;

class Blocked extends \Movim\Widget\Base
{
    public function load()
    {
        $this->addcss('blocked.css');
        $this->addjs('blocked.js');
        $this->registerEvent('blocking_request_handle', 'onList');
        $this->registerEvent('blocking_unblock_handle', 'onUnblock');
        $this->registerEvent('blocked', 'onList');
        $this->registerEvent('unblocked', 'onList');
    }

    public function onList()
    {
        $list = $this->tpl();
        $list->assign('list', $this->me->reported()->orderBy('reported_user.created_at', 'desc')->get());
        $this->rpc('MovimTpl.fill', '#blocked_widget_list', $list->draw('_blocked_list'));
    }

    public function onUnblock(Packet $packet)
    {
        Toast::send($this->__('blocked.account_unblocked'));
        $this->rpc('MovimTpl.remove', '#blocked-' . cleanupId($packet->content));
    }

    public function ajaxGet()
    {
        $this->onList();
    }

    public function ajaxUnblock(string $jid)
    {
        $unblock = new Unblock;
        $unblock->setJid($jid);
        $unblock->request();
    }
}
