<?php

use Movim\Widget\Base;

use Moxl\Xec\Action\Roster\UpdateItem;
use Moxl\Xec\Action\Roster\RemoveItem;
use Moxl\Xec\Action\Presence\Unsubscribe;

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

    public function onUpdate($packet)
    {
        $this->rpc('MovimTpl.fill', '#'.cleanupId($packet->content) . '_contact_header', $this->prepareHeader($packet->content));
    }

    public function onVcardReceived($packet)
    {
        $this->rpc('MovimTpl.fill', '#'.cleanupId($packet->content->id) . '_contact_header', $this->prepareHeader($packet->content->id));
    }

    public function ajaxEditContact($jid)
    {
        if (!validateJid($jid)) {
            return;
        }

        $view = $this->tpl();

        $view->assign('contact', $this->user->session->contacts()->where('jid', $jid)->first());
        $view->assign('groups', $this->user->session->contacts()->select('group')->groupBy('group')->pluck('group')->toArray());

        Dialog::fill($view->draw('_contactheader_edit'));
    }

    public function ajaxEditSubmit($form)
    {
        $rd = new UpdateItem;
        $rd->setTo(echapJid($form->jid->value))
           ->setName($form->alias->value)
           ->setGroup($form->group->value)
           ->request();
    }

    public function ajaxDeleteContact($jid)
    {
        if (!validateJid($jid)) {
            return;
        }

        $view = $this->tpl();
        $view->assign('jid', $jid);

        Dialog::fill($view->draw('_contactheader_delete'));
    }

    /**
     * @brief Remove a contact to the roster and unsubscribe
     */
    public function ajaxDelete($jid)
    {
        $r = new RemoveItem;
        $r->setTo($jid)
          ->request();

        $p = new Unsubscribe;
        $p->setTo($jid)
          ->request();
    }

    public function ajaxChat($jid)
    {
        if (!validateJid($jid)) {
            return;
        }

        $c = new Chats;
        $c->ajaxOpen($jid);

        $this->rpc('MovimUtils.redirect', $this->route('chat', $jid));
    }

    public function prepareHeader($jid)
    {
        if (!validateJid($jid)) {
            return;
        }

        $view = $this->tpl();
        $view->assign('roster', ($this->user->session->contacts()->where('jid', $jid)->first()));
        $view->assign('contact', App\Contact::firstOrNew(['id' => $jid]));

        return $view->draw('_contactheader');
    }

    public function display()
    {
        $this->view->assign('jid', $this->get('s'));
    }
}
