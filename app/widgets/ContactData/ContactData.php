<?php

use Respect\Validation\Validator;

class ContactData extends \Movim\Widget\Base
{
    public function load()
    {
    }

    public function prepareData($jid)
    {
        $view = $this->tpl();

        $id = new \Modl\ItemDAO;
        $cd = new \Modl\ContactDAO;

        $view = $this->tpl();

        $contactr = $cd->getRosterItem($jid);

        $view->assign('mood', getMood());
        $view->assign('clienttype', getClientTypes());
        $view->assign('contact', $cd->get($jid));
        $view->assign('contactr', $contactr);
        $view->assign('subscriptions', $id->getSharedItems($jid));

        if($contactr->value != null) {
            $view->assign('presence', getPresencesTxt()[$contactr->value]);
        }

        if(isset($contactr)) {
            $view->assign('caps', $contactr->getCaps());
        }

        return $view->draw('_contactdata', true);
    }

    public function display()
    {
        $this->view->assign('jid', $this->get('s'));
    }
}
