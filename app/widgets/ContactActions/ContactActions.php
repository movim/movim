<?php

use Respect\Validation\Validator;

class ContactActions extends \Movim\Widget\Base
{
    function ajaxAddAsk($jid)
    {
        $view = $this->tpl();
        $view->assign('contact', App\Contact::firstOrNew(['id' => $jid]));
        $view->assign('groups', $this->user->session->contacts()->pluck('group')->toArray());

        Dialog::fill($view->draw('_contactactions_add', true));
    }

    function ajaxGetDrawer($jid)
    {
        if (!$this->validateJid($jid)) return;

        $tpl = $this->tpl();
        $tpl->assign('contact', App\Contact::firstOrNew(['id' => $jid]));
        $tpl->assign('roster', $this->user->session->contacts()->where('jid', $jid)->first());
        $tpl->assign('clienttype', getClientTypes());

        Drawer::fill($tpl->draw('_contactactions_drawer', true));
    }

    function ajaxAdd($form)
    {
        $roster = new Roster;
        $roster->ajaxAdd($form);
    }

    function ajaxChat($jid)
    {
        if (!$this->validateJid($jid)) return;

        $c = new Chats;
        $c->ajaxOpen($jid);

        $this->rpc('MovimUtils.redirect', $this->route('chat', $jid));
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
}
