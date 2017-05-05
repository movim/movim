<?php

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

    function ajaxAdd($form)
    {
        $roster = new Roster;
        $roster->ajaxAdd($form);
    }

    function display()
    {
    }
}
