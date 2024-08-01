<?php

namespace App\Widgets\ContactData;

use Movim\CurrentCall;
use Movim\Widget\Base;

class ContactData extends Base
{
    public function load()
    {
        $this->addjs('contactdata.js');
        $this->registerEvent('vcard_get_handle', 'onVcardReceived', 'contact');
        $this->registerEvent('vcard4_get_handle', 'onVcardReceived', 'contact');

        $this->registerEvent('currentcall_started', 'onCallEvent', 'contact');
        $this->registerEvent('currentcall_stopped', 'onCallEvent', 'contact');
    }

    public function onVcardReceived($packet)
    {
        $contact = $packet->content;
        $this->ajaxGet($contact->id);
    }

    public function onCallEvent($packet)
    {
        $this->ajaxGet($packet[0]);
    }

    public function prepareData($jid)
    {
        if (!validateJid($jid)) {
            return;
        }

        $view = $this->tpl();

        $view->assign(
            'message',
            \App\Message::jid($jid)
                        ->orderBy('published', 'desc')
                        ->first()
        );
        $view->assign('contact', \App\Contact::firstOrNew(['id' => $jid]));
        $view->assign('roster', $this->user->session->contacts()->where('jid', $jid)->first());
        $view->assign('incall', CurrentCall::getInstance()->isStarted());

        return $view->draw('_contactdata');
    }

    public function prepareCard($contact, $roster = null)
    {
        $view = $this->tpl();
        $view->assign('contact', $contact);
        $view->assign('roster', $roster);

        return $view->draw('_contactdata_card');
    }

    public function ajaxGet($jid)
    {
        if (!validateJid($jid)) {
            return;
        }

        $this->rpc('MovimTpl.fill', '#'.cleanupId($jid) . '_contact_data', $this->prepareData($jid));
        $this->rpc('Notif_ajaxGet');
    }

    public function ajaxRefresh($jid)
    {
        if (!validateJid($jid)) {
            return;
        }

        $contact = \App\Contact::find($jid);

        if (!$contact || $contact->isOld()) {
            $a = new \Moxl\Xec\Action\Avatar\Get;
            $a->setTo(echapJid($jid))->request();

            $a = new \Moxl\Xec\Action\Banner\Get;
            $a->setTo(echapJid($jid))->request();

            $v = new \Moxl\Xec\Action\Vcard\Get;
            $v->setTo(echapJid($jid))->request();

            $r = new \Moxl\Xec\Action\Vcard4\Get;
            $r->setTo(echapJid($jid))->request();
        } else if ($contact) {
            $this->rpc('Notif_ajaxGet');
        }
    }

    public function display()
    {
        $this->view->assign('jid', $this->get('s'));
    }
}
