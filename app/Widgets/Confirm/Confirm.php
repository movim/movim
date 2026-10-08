<?php

namespace App\Widgets\Confirm;

use Movim\Widget\Base;

use Moxl\Xec\Action\Confirm\Accept;
use Moxl\Xec\Action\Confirm\Refuse;
use Moxl\Xec\Payload\Packet;

class Confirm extends Base
{
    public function load()
    {
        $this->addcss('confirm.css');
        $this->registerEvent('confirm', 'onConfirm');
    }

    public function onConfirm(Packet $packet)
    {
        $view = $this->tpl();

        $view->assign('from', $packet->content['from']);
        $view->assign('id', $packet->content['id']);
        $view->assign('url', $packet->content['url']);
        $view->assign('method', $packet->content['method']);

        $this->dialog($view->draw('_confirm'));
    }

    public function ajaxAccept($to, $id, $url, $method)
    {
        $accept = $this->xmpp(new Accept);
        $accept->setTo($to)
               ->setId($id)
               ->setUrl($url)
               ->setMethod($method)
               ->request();
    }

    public function ajaxRefuse($to, $id, $url, $method)
    {
        $refuse = $this->xmpp(new Refuse);
        $refuse->setTo($to)
               ->setId($id)
               ->setUrl($url)
               ->setMethod($method)
               ->request();
    }
}
