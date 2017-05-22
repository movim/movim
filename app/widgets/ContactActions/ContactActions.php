<?php

use Respect\Validation\Validator;

class ContactActions extends \Movim\Widget\Base
{
    function load()
    {
    }

    function ajaxAddAsk($jid)
    {
        $cd = new \Modl\ContactDAO;
        $contact = $cd->get($jid);

        if($contact) {
            $view = $this->tpl();
            $rd = new \Modl\RosterLinkDAO;

            $view->assign('contact', $contact);
            $view->assign('groups', $rd->getGroups());

            Dialog::fill($view->draw('_contactactions_add', true));
        }
    }

    function ajaxGetDrawer($jid)
    {
        if(!$this->validateJid($jid)) return;

        $tpl = $this->tpl();

        $cd = new \Modl\ContactDAO;
        $cr = $cd->getRosterItem($jid);

        if(isset($cr)) {
            if($cr->value != null) {
                $tpl->assign('presence', getPresencesTxt()[$cr->value]);
            }

            $tpl->assign('contactr', $cr);
            $tpl->assign('caps', $cr->getCaps());
            $tpl->assign('clienttype', getClientTypes());
        }

        $c  = $cd->get($jid);
        $tpl->assign('contact', $c);

        Drawer::fill($tpl->draw('_contactactions_drawer', true));
    }

    function ajaxAdd($form)
    {
        $roster = new Roster;
        $roster->ajaxAdd($form);
    }

    function ajaxChat($jid)
    {
        if(!$this->validateJid($jid)) return;

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
        if(!$validate_jid->validate($jid)) return false;
        else return true;
    }

    function display()
    {
    }
}
