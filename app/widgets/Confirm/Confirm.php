<?php

use Moxl\Xec\Action\Confirm\Accept;
use Moxl\Xec\Action\Confirm\Refuse;

class Confirm extends \Movim\Widget\Base
{
    function load()
    {
        $this->addcss('confirm.css');
        $this->registerEvent('confirm', 'onConfirm');
    }

    function onConfirm($package)
    {
        $view = $this->tpl();

        $view->assign('from', $package->content['from']);
        $view->assign('id', $package->content['id']);
        $view->assign('url', $package->content['url']);
        $view->assign('method', $package->content['method']);

        Dialog::fill($view->draw('_confirm'));
    }

    function ajaxAccept($to, $id, $url, $method)
    {
        $accept = new Accept;
        $accept->setTo($to)
               ->setId($id)
               ->setUrl($url)
               ->setMethod($method)
               ->request();
    }

    function ajaxRefuse($to, $id, $url, $method)
    {
        $refuse = new Refuse;
        $refuse->setTo($to)
               ->setId($id)
               ->setUrl($url)
               ->setMethod($method)
               ->request();
    }
}
