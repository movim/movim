<?php

namespace App\Widgets\ContactHeader;

use App\Widgets\Chats\Chats;
use App\Widgets\Dialog\Dialog;
use Movim\Widget\Base;

use Moxl\Xec\Action\Roster\UpdateItem;
use Moxl\Xec\Payload\Packet;

class ContactHeader extends Base
{
    public function load()
    {
        $this->registerEvent('roster_additem_handle', 'onUpdate');
        $this->registerEvent('roster_updateitem_handle', 'onUpdate', 'contact');
        $this->registerEvent('roster_removeitem_handle', 'onUpdate', 'contact');
        $this->registerEvent('vcard_get_handle', 'onVcardReceived', 'contact');
        $this->registerEvent('vcard4_get_handle', 'onVcardReceived', 'contact');
    }

    public function onUpdate(Packet $packet)
    {
        $this->rpc('MovimTpl.fill', '#' . cleanupId($packet->content) . '_contact_header', $this->prepareHeader($packet->content));
    }

    public function onVcardReceived(Packet $packet)
    {
        $this->rpc('MovimTpl.fill', '#' . cleanupId($packet->content) . '_contact_header', $this->prepareHeader($packet->content));
    }

    public function ajaxEditContact($jid)
    {
        if (!validateJid($jid)) {
            return;
        }

        $view = $this->tpl();

        $view->assign('contact', $this->me->session->contacts()->where('jid', $jid)->first());
        $view->assign('groups', $this->me->session->contacts()->select('group')->groupBy('group')->pluck('group')->toArray());

        Dialog::fill($view->draw('_contactheader_edit'));
    }

    public function ajaxEditSubmit($form)
    {
        $rd = new UpdateItem;
        $rd->setTo($form->jid->value)
            ->setName($form->alias->value)
            ->setGroup($form->group->value)
            ->request();
    }

    public function ajaxChat($jid)
    {
        if (!validateJid($jid)) {
            return;
        }

        $c = new Chats();
        $c->ajaxOpen($jid, andShow: true);

        $this->rpc('MovimUtils.redirect', $this->route('chat', $jid));
    }

    public function prepareHeader($jid)
    {
        if (!validateJid($jid)) {
            return;
        }

        $view = $this->tpl();
        $view->assign('roster', ($this->me->session->contacts()->where('jid', $jid)->first()));
        $view->assign('contact', \App\Contact::firstOrNew(['id' => $jid]));

        return $view->draw('_contactheader');
    }

    public function display()
    {
        $this->view->assign('jid', $this->get('s'));
    }
}
