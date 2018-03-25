<?php

use Moxl\Xec\Action\Roster\UpdateItem;
use Moxl\Xec\Action\Roster\RemoveItem;
use Moxl\Xec\Action\Presence\Unsubscribe;

use Respect\Validation\Validator;
use App\Roster as DBRoster;

class ContactHeader extends \Movim\Widget\Base
{
    public function load()
    {
        $this->registerEvent('roster_additem_handle', 'onUpdate');
        $this->registerEvent('roster_updateitem_handle', 'onUpdate');
        $this->registerEvent('roster_removeitem_handle', 'onUpdate');
        $this->registerEvent('roster_getlist_handle', 'onUpdate');
    }

    function onUpdate($packet)
    {
        $this->rpc('MovimTpl.fill', '#contact_header', $this->prepareHeader($packet->content));
    }

    function ajaxEditContact($jid)
    {
        if (!$this->validateJid($jid)) return;

        $view = $this->tpl();

        $view->assign('contact', App\User::me()->session->contacts->where('jid', $jid)->first());
        $view->assign('groups', App\User::me()->session->contacts->pluck('group')->toArray());

        Dialog::fill($view->draw('_contactheader_edit', true));
    }

    function ajaxEditSubmit($form)
    {
        $rd = new UpdateItem;
        $rd->setTo(echapJid($form->jid->value))
           ->setName($form->alias->value)
           ->setGroup($form->group->value)
           ->request();
    }

    function ajaxDeleteContact($jid)
    {
        if (!$this->validateJid($jid)) return;

        $view = $this->tpl();
        $view->assign('jid', $jid);

        Dialog::fill($view->draw('_contactheader_delete', true));
    }

    /**
     * @brief Remove a contact to the roster and unsubscribe
     */
    function ajaxDelete($jid)
    {
        $r = new RemoveItem;
        $r->setTo($jid)
          ->request();

        $p = new Unsubscribe;
        $p->setTo($jid)
          ->request();
    }

    function ajaxChat($jid)
    {
        if (!$this->validateJid($jid)) return;

        $c = new Chats;
        $c->ajaxOpen($jid);

        $this->rpc('MovimUtils.redirect', $this->route('chat', $jid));
    }

    public function prepareHeader($jid)
    {
        $view = $this->tpl();
        $view->assign('in_roster', (App\User::me()->session->contacts->where('jid', $jid)->count() > 0));
        $view->assign('contact', App\Contact::firstOrNew(['id' => $jid]));

        return $view->draw('_contactheader', true);
    }

    /**
     * @brief Validate the jid
     *
     * @param string $jid
     */
    private function validateJid($jid)
    {
        $validate_jid = Validator::stringType()->noWhitespace()->length(6, 60);
        return ($validate_jid->validate($jid));
    }

    public function display()
    {
        $this->view->assign('jid', $this->get('s'));
    }
}
