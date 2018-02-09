<?php

use Moxl\Xec\Action\AdHoc\Get;
use Moxl\Xec\Action\AdHoc\Command;
use Moxl\Xec\Action\AdHoc\Submit;

use Movim\Session;

class AdHoc extends \Movim\Widget\Base
{
    function load()
    {
        $this->addjs('adhoc.js');
        $this->registerEvent('adhoc_get_handle', 'onList');
        $this->registerEvent('adhoc_command_handle', 'onCommand');
        $this->registerEvent('adhoc_submit_handle', 'onCommand');
        $this->registerEvent('adhoc_command_error', 'onCommandError');
        $this->registerEvent('adhoc_submit_error', 'onCommandError');
    }

    function onList($package)
    {
        $list = $package->content;
        $html = $this->prepareList($list);
        $this->rpc('MovimTpl.fill', '#adhoc_widget', $html);
        $this->rpc('AdHoc.refresh');
    }

    function onCommand($package)
    {
        $command = $package->content;

        $view = $this->tpl();
        $view->assign('jid', $package->from);

        if(isset($command->note)) {
            $view->assign('note', $command->note);

            Dialog::fill($view->draw('_adhoc_note', true));
        }

        if(isset($command->x)) {
            $xml = new \XMPPtoForm();
            $form = $xml->getHTML($command->x->asXML());

            $view->assign('form', $form);
            $view->assign('attributes', $command->attributes());
            $view->assign('actions', null);
            if(isset($command->actions)) {
                $view->assign('actions', $command->actions);
            }

            Dialog::fill($view->draw('_adhoc_form', true), true);
        }

        $this->rpc('AdHoc.initForm');
    }

    function prepareList($list)
    {
        $view = $this->tpl();
        $view->assign('list', $list);
        return $view->draw('_adhoc_list', true);
    }

    function onCommandError($package)
    {
        $view = $this->tpl();

        $note = $package->content['errorid'];
        if($package->content['message']) {
            $note = $package->content['message'];
        }

        $view->assign('note', $note);
        Dialog::fill($view->draw('_adhoc_note', true), true);
    }

    function ajaxGet($jid)
    {
        if(!$jid) {
            $session = Session::start();
            $jid = $session->get('host');
        }

        $g = new Get;
        $g->setTo($jid)->request();
    }

    function ajaxCommand($jid, $node)
    {
        $c = new Command;
        $c->setTo($jid)
          ->setNode($node)
          ->request();
    }

    function ajaxSubmit($jid, $data, $node, $sessionid)
    {
        if(!$jid) {
            $session = Session::start();
            $jid = $session->get('host');
        }

        $s = new Submit;
        $s->setTo($jid)
          ->setNode($node)
          ->setData($data)
          ->setSessionid($sessionid)
          ->request();
    }

    function getIcon($command)
    {
        $icons = [
            'http://jabber.org/protocol/admin#delete-user' => 'zmdi-delete',
            'http://jabber.org/protocol/admin#end-user-session' => 'zmdi-stop',
            'http://jabber.org/protocol/admin#change-user-password' => 'zmdi-lock',
            'ping' => 'zmdi-swap',
            'http://jabber.org/protocol/admin#shutdown' => 'zmdi-power-off',
            'http://jabber.org/protocol/admin#add-user' => 'zmdi-account-add',
            'http://jabber.org/protocol/admin#user-stats' => 'zmdi-accounts',
            'uptime' => 'zmdi-time',
            'http://jabber.org/protocol/admin#server-buddy' => 'zmdi-stop',
            'http://jabber.org/protocol/admin#get-user-roster' => 'zmdi-format-list-bulleted',
            'http://jabber.org/protocol/admin#get-online-users' => 'zmdi-trending-up',
            'http://jabber.org/protocol/admin#announce' => 'zmdi-notifications',
        ];

        if(array_key_exists($command, $icons)) {
            return $icons[$command];
        }

        return 'zmdi-chevron-right';
    }
}
