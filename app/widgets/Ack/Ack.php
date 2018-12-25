<?php

use Moxl\Xec\Action\Ack\Send;
use Moxl\Xec\Action\Disco\Request;
use Moxl\Stanza\Disco;

class Ack extends \Movim\Widget\Base
{
    public function load()
    {
        $this->registerEvent('ack', 'onAckRequest');
    }

    public function onAckRequest($ack)
    {
        $to = $ack[0];
        $id = $ack[1];
        $this->rpc('ackRequest', $to, $id);
    }

    public function ajaxAckRequest($to, $id)
    {
        $ack = new Send;
        $ack->setTo($to)
            ->setId($id)
            ->request();
    }

    public function display()
    {
        $this->view->assign('ack', $this->call('ajaxAckRequest', 'to', 'id'));
    }
}
