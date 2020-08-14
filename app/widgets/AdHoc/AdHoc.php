<?php

use Moxl\Xec\Action\AdHoc\Get;
use Moxl\Xec\Action\AdHoc\Command;
use Moxl\Xec\Action\AdHoc\Submit;

use Movim\Session;

class AdHoc extends \Movim\Widget\Base
{
    public function load()
    {
        $this->addjs('adhoc.js');
        $this->registerEvent('adhoc_get_handle', 'onList');
        $this->registerEvent('adhoc_command_handle', 'onCommand');
        $this->registerEvent('adhoc_submit_handle', 'onCommand');
        $this->registerEvent('adhoc_command_error', 'onCommandError');
        $this->registerEvent('adhoc_submit_error', 'onCommandError');
    }

    public function onList($package)
    {
        $list = $package->content;
        $html = $this->prepareList($list);
        $this->rpc('MovimTpl.fill', '#adhoc_widget', $html);
        $this->rpc('AdHoc.refresh');
    }

    public function onCommand($package)
    {
        $command = $package->content;
        $attributes = (array)$command->attributes();

        $view = $this->tpl();
        $view->assign('jid', $package->from);

        if (isset($command->note)) {
            $view->assign('note', $command->note);
            Dialog::fill($view->draw('_adhoc_note'));
            $this->rpc('AdHoc.initForm');
        } elseif (isset($command->x)) {
            $xml = new \XMPPtoForm();
            $form = $xml->getHTML($command->x);

            $view->assign('form', $form);
            $view->assign('attributes', $command->attributes());
            $view->assign('actions', null);
            if (isset($command->actions)) {
                $view->assign('actions', $command->actions);
            }

            Dialog::fill($view->draw('_adhoc_form'), true);
            $this->rpc('AdHoc.initForm');
        } elseif ((string)$command->attributes()->status === 'completed') {
            $this->rpc('Dialog.clear');
            Toast::send($this->__('adhoc.completed'));
            return;
        }
    }

    public function prepareList($list)
    {
        $view = $this->tpl();
        $view->assign('list', $list);
        return $view->draw('_adhoc_list');
    }

    public function onCommandError($package)
    {
        $view = $this->tpl();

        $note = $package->content['errorid'];
        if ($package->content['message']) {
            $note = $package->content['message'];
        }

        $view->assign('note', $note);
        Dialog::fill($view->draw('_adhoc_note'), true);
    }

    public function ajaxGet($jid)
    {
        if (!$jid) {
            $session = Session::start();
            $jid = $session->get('host');
        }

        $g = new Get;
        $g->setTo($jid)->request();
    }

    public function ajaxCommand($jid, $node)
    {
        $c = new Command;
        $c->setTo($jid)
          ->setNode($node)
          ->request();
    }

    public function ajaxSubmit($jid, $data, $node, $sessionid)
    {
        if (!$jid) {
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

    public function getIcon($command)
    {
        $icons = [
            'http://jabber.org/protocol/admin#delete-user' => 'delete',
            'http://jabber.org/protocol/admin#end-user-session' => 'stop',
            'http://jabber.org/protocol/admin#change-user-password' => 'lock',
            'ping' => 'swap_horiz',
            'http://jabber.org/protocol/admin#shutdown' => 'power_off',
            'http://jabber.org/protocol/admin#add-user' => 'person_add',
            'http://jabber.org/protocol/admin#user-stats' => 'people',
            'uptime' => 'timer',
            'http://jabber.org/protocol/admin#server-buddy' => 'stop',
            'http://jabber.org/protocol/admin#get-user-roster' => 'format_list_bulleted',
            'http://jabber.org/protocol/admin#get-online-users' => 'trending_up',
            'http://jabber.org/protocol/admin#announce' => 'notifications',
        ];

        if (array_key_exists($command, $icons)) {
            return $icons[$command];
        }

        return 'chevron_right';
    }
}
