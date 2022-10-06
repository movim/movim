<?php

use Movim\Widget\Base;

class ContactData extends Base
{
    public function load()
    {
        $this->addjs('contactdata.js');
        $this->registerEvent('vcard_get_handle', 'onVcardReceived', 'contact');
        $this->registerEvent('vcard4_get_handle', 'onVcardReceived', 'contact');
    }

    public function onVcardReceived($packet)
    {
        $contact = $packet->content;
        $this->rpc('MovimTpl.fill', '#'.cleanupId($contact->id) . '_contact_data', $this->prepareData($contact->id));
        $this->rpc('Notification_ajaxGet');
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
        $view->assign('contact', App\Contact::firstOrNew(['id' => $jid]));
        $view->assign('roster', $this->user->session->contacts()->where('jid', $jid)->first());

        return $view->draw('_contactdata');
    }

    public function prepareCard($contact, $roster = null)
    {
        $view = $this->tpl();
        $view->assign('contact', $contact);
        $view->assign('roster', $roster);

        return $view->draw('_contactdata_card');
    }

    public function ajaxRefresh($jid)
    {
        if (!validateJid($jid)) {
            return;
        }

        $contact = \App\Contact::find($jid);

        if (!$contact || $contact->isOld()) {
            $a = new Moxl\Xec\Action\Avatar\Get;
            $a->setTo(echapJid($jid))->request();

            $v = new Moxl\Xec\Action\Vcard\Get;
            $v->setTo(echapJid($jid))->request();

            $r = new Moxl\Xec\Action\Vcard4\Get;
            $r->setTo(echapJid($jid))->request();
        } else if ($contact) {
            $this->rpc('MovimTpl.fill', '#'.cleanupId($jid) . '_contact_data', $this->prepareData($jid));
            $this->rpc('Notification_ajaxGet');
        }
    }

    public function display()
    {
        $this->view->assign('jid', $this->get('s'));
    }
}
