<?php

namespace App\Widgets\AdHoc;

use App\Widgets\Dialog\Dialog;
use Movim\Librairies\JingletoSDP;
use Movim\Librairies\SDPtoJingle;
use Moxl\Xec\Action\AdHoc\Get;
use Moxl\Xec\Action\AdHoc\Command;
use Moxl\Xec\Action\AdHoc\Submit;

use Movim\Session;
use Moxl\Xec\Payload\Packet;
use stdClass;

class AdHoc extends \Movim\Widget\Base
{
    public function load()
    {
        $this->addjs('adhoc.js');
        $this->registerEvent('adhoc_get_handle', 'onList');
        $this->registerEvent('adhoc_get_error', 'onListError');
        $this->registerEvent('adhoc_command_handle', 'onCommand');
        $this->registerEvent('adhoc_submit_handle', 'onCommand');
        $this->registerEvent('adhoc_command_error', 'onCommandError');
        $this->registerEvent('adhoc_submit_error', 'onCommandError');
    }

    public function onList(Packet $packet)
    {
        if (empty($packet->content)) {
            $this->onListError($packet);
        } else {
            $view = $this->tpl();
            $view->assign('list', $packet->content);
            $this->rpc('MovimTpl.fill', '#adhoc_widget_' . cleanupId($packet->from), $view->draw('_adhoc_list'));
            $this->rpc('AdHoc.refresh');
        }
    }

    public function onListError(Packet $packet)
    {
        $this->rpc('MovimTpl.remove', '#adhoc_widget_' . cleanupId($packet->from));
        $this->rpc('Tabs.create');
    }

    public function onCommand(Packet $packet)
    {
        $command = $packet->content;

        $view = $this->tpl();
        $view->assign('jid', $packet->from);

        if (isset($command->note)) {
            $view->assign('note', $command->note);
            Dialog::fill($view->draw('_adhoc_note'));
            $this->rpc('AdHoc.initForm');
        } elseif (isset($command->x)) {
            $xml = new \Movim\Librairies\XMPPtoForm;
            $form = $xml->getHTML($command->x);

            $view->assign('form', $form);
            $view->assign('attributes', $command->attributes());
            $view->assign('actions', null);
            $view->assign('status', (string)$command->attributes()->status);
            if (isset($command->actions)) {
                $view->assign('actions', $command->actions);
            }

            Dialog::fill($view->draw('_adhoc_form'), true);
            $this->rpc('AdHoc.initForm');
        } elseif ((string)$command->attributes()->status === 'completed') {
            $this->rpc('Dialog.clear');
            $this->toast($this->__('adhoc.completed'));
            return;
        }
    }

    public function ajaxSDPToJingle()
    {
        $view = $this->tpl();
        Dialog::fill($view->draw('_adhoc_sdptojingle'), true);
    }

    public function ajaxSDPToJingleSubmit(stdClass $data)
    {
        $stj = new SDPtoJingle($data->sdp->value, 'SID');

        $view = $this->tpl();
        $view->assign('jingle', $stj->generate());
        Dialog::fill($view->draw('_adhoc_sdptojingle_result'), true);
    }

    public function ajaxJingleToSDP()
    {
        $view = $this->tpl();
        Dialog::fill($view->draw('_adhoc_jingletosdp'), true);
    }

    public function ajaxJingleToSDPSubmit(stdClass $data)
    {
        $xml = simplexml_load_string($data->jingle->value);

        if ($xml == false) {
            $this->toast($this->__('error.oops'));
            return;
        }

        $jts = new JingletoSDP($xml);

        $view = $this->tpl();
        $view->assign('sdp', $jts->generate());
        Dialog::fill($view->draw('_adhoc_jingletosdp_result'), true);
    }

    public function onCommandError(Packet $packet)
    {
        $view = $this->tpl();

        $note = $packet->content['errorid'];
        if ($packet->content['message']) {
            $note = $packet->content['message'];
        }

        $view->assign('note', $note);
        Dialog::fill($view->draw('_adhoc_note'), true);
    }

    public function ajaxGet(?string $jid = null)
    {
        if ($jid == null) {
            $jid = Session::instance()->get('host');
        }

        $g = new Get;
        $g->setTo($jid)->request();
    }

    public function ajaxCommand(string $jid, string $node)
    {
        $c = new Command;
        $c->setTo($jid)
          ->setNode($node)
          ->request();
    }

    public function ajaxSubmit(string $jid, string $node, $data, $sessionid)
    {
        $s = new Submit;
        $s->setTo($jid)
          ->setNode($node)
          ->setData(formToArray($data))
          ->setSessionid($sessionid)
          ->request();
    }

    public function getIcon($command)
    {
        $icons = [
            'http://jabber.org/protocol/admin#delete-user' => 'delete',
            'http://jabber.org/protocol/admin#end-user-session' => 'stop',
            'http://jabber.org/protocol/admin#change-user-password' => 'lock',
            'ping' => 'network_ping',
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

        return 'list_alt';
    }

    public function display(?string $to = null)
    {
        $this->view->assign('to', $to);
    }
}
