<?php

use Moxl\Xec\Action\Pubsub\Delete;

use Moxl\Xec\Action\Pubsub\GetAffiliations;
use Moxl\Xec\Action\Pubsub\GetSubscriptions;

use Moxl\Xec\Action\Pubsub\GetConfig;
use Moxl\Xec\Action\Pubsub\SetConfig;

use Respect\Validation\Validator;

class CommunityAffiliations extends \Movim\Widget\Base
{
    public function load()
    {
        $this->registerEvent('pubsub_getaffiliations_handle', 'onAffiliations');
        $this->registerEvent('pubsub_delete_handle', 'onDelete');
        $this->registerEvent('pubsub_delete_error', 'onDeleteError');

        $this->registerEvent('pubsub_getsubscriptions_handle', 'onSubscriptions');

        $this->registerEvent('pubsub_getconfig_handle', 'onConfig');
        $this->registerEvent('pubsub_setconfig_handle', 'onConfigSaved');

        $this->addjs('communityaffiliations.js');
    }

    function onAffiliations($packet)
    {
        list($affiliations, $server, $node) = array_values($packet->content);

        $role = null;

        foreach($affiliations as $r) {
            if($r[0] == $this->user->getLogin())
                $role = (string)$r[1];
        }

        $id = new \Modl\ItemDAO;
        $item = $id->getItem($server, $node);

        $view = $this->tpl();
        $view->assign('role', $role);
        $view->assign('item', $item);

        $this->rpc('MovimTpl.fill', '#community_affiliation', $view->draw('_communityaffiliations', true));
    }

    function onSubscriptions($packet)
    {
        list($subscriptions, $server, $node) = array_values($packet->content);

        $view = $this->tpl();

        $view->assign('subscriptions', $subscriptions);
        $view->assign('server', $server);
        $view->assign('node', $node);

        Dialog::fill($view->draw('_communityaffiliations_subscriptions', true), true);
    }

    function onConfig($packet)
    {
        list($config, $server, $node) = array_values($packet->content);

        $view = $this->tpl();

        $xml = new \XMPPtoForm();
        $form = $xml->getHTML($config->x->asXML());

        $view->assign('form', $form);
        $view->assign('server', $server);
        $view->assign('node', $node);
        $view->assign('attributes', $config->attributes());

        Dialog::fill($view->draw('_communityaffiliations_config', true), true);
    }

    function onConfigSaved()
    {
        Notification::append(false, $this->__('communityaffiliation.config_saved'));
    }

    private function deleted($packet)
    {
        if($packet->content['server'] != $this->user->getLogin()
        && substr($packet->content['node'], 0, 29) != 'urn:xmpp:microblog:0:comments') {
            Notification::append(null, $this->__('communityaffiliation.deleted'));

            $this->rpc('MovimUtils.redirect',
                $this->route('community',
                    [$packet->content['server']]
                )
            );
        }
    }

    function onDelete($packet)
    {
        Notification::append(null, $this->__('communityaffiliation.deleted'));

        $this->deleted($packet);
    }

    function onDeleteError($packet)
    {
        $m = new Rooms;
        $m->setBookmark();

        $this->deleted($packet);
    }

    function ajaxGetAffiliations($server, $node){
        if(!$this->validateServerNode($server, $node)) return;

        $r = new GetAffiliations;
        $r->setTo($server)->setNode($node)
          ->request();
    }

    function ajaxGetSubscriptions($server, $node, $notify = true)
    {
        if(!$this->validateServerNode($server, $node)) return;

        $r = new GetSubscriptions;
        $r->setTo($server)
          ->setNode($node)
          ->setNotify($notify)
          ->request();
    }

    function ajaxGetConfig($server, $node)
    {
        if(!$this->validateServerNode($server, $node)) return;

        $r = new GetConfig;
        $r->setTo($server)
          ->setNode($node)
          ->request();
    }

    function ajaxSetConfig($data, $server, $node)
    {
        if(!$this->validateServerNode($server, $node)) return;

        $r = new SetConfig;
        $r->setTo($server)
          ->setNode($node)
          ->setData($data)
          ->request();
    }

    function ajaxDelete($server, $node, $clean = false)
    {
        if(!$this->validateServerNode($server, $node)) return;

        $view = $this->tpl();
        $view->assign('server', $server);
        $view->assign('node', $node);
        $view->assign('clean', $clean);

        Dialog::fill($view->draw('_communityaffiliations_delete', true));
    }

    function ajaxDeleteConfirm($server, $node)
    {
        if(!$this->validateServerNode($server, $node)) return;

        $d = new Delete;
        $d->setTo($server)->setNode($node)
          ->request();
    }

    private function validateServerNode($server, $node)
    {
        $validate_server = Validator::stringType()->noWhitespace()->length(6, 40);
        $validate_node = Validator::stringType()->length(3, 100);

        if(!$validate_server->validate($server)
        || !$validate_node->validate($node)
        ) return false;
        else return true;
    }

    public function display()
    {
    }
}
