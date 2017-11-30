<?php

use Moxl\Xec\Action\Pubsub\GetConfig;
use Moxl\Xec\Action\Pubsub\SetConfig;

use Respect\Validation\Validator;

class CommunityConfig extends \Movim\Widget\Base
{
    public function load()
    {
        $this->registerEvent('pubsub_getconfig_handle', 'onConfig');
        $this->registerEvent('pubsub_setconfig_handle', 'onConfigSaved');
    }

    function onConfig($packet)
    {
        list($config, $origin, $node, $advanced) = array_values($packet->content);

        $view = $this->tpl();

        $xml = new \XMPPtoForm;
        $form = $xml->getHTML($config->x->asXML());

        $view->assign('form', $form);
        $view->assign('server', $origin);
        $view->assign('node', $node);
        $view->assign('config', ($advanced) ? false : $xml->getArray($config->x));
        $view->assign('attributes', $config->attributes());

        Dialog::fill($view->draw('_communityconfig', true), true);
    }

    function onConfigSaved()
    {
        Notification::append(false, $this->__('communityaffiliation.config_saved'));
    }

    function ajaxGetConfig($origin, $node, $advanced = false)
    {
        if(!$this->validateServerNode($origin, $node)) return;

        $r = new GetConfig;
        $r->setTo($origin)
          ->setNode($node);

        if($advanced) {
            $r->enableAdvanced();
        }

        $r->request();
    }

    function ajaxSetConfig($data, $origin, $node)
    {
        if(!$this->validateServerNode($origin, $node)) return;

        $r = new SetConfig;
        $r->setTo($origin)
          ->setNode($node)
          ->setData($data)
          ->request();
    }

    private function validateServerNode($origin, $node)
    {
        $validate_server = Validator::stringType()->noWhitespace()->length(6, 40);
        $validate_node = Validator::stringType()->length(3, 100);

        return ($validate_server->validate($origin)
             && $validate_node->validate($node));
    }
}
