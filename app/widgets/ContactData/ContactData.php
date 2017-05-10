<?php

use Respect\Validation\Validator;

class ContactData extends \Movim\Widget\Base
{
    public function load()
    {
        $this->addjs('contactdata.js');
        $this->registerEvent('vcard_get_handle', 'onVcardReceived');
        $this->registerEvent('vcard4_get_handle', 'onVcardReceived');
    }

    public function onVcardReceived($packet)
    {
        $contact = $packet->content;
        $this->rpc('MovimTpl.fill', '#contact_data', $this->prepareData($contact->jid));
    }

    public function prepareData($jid)
    {
        $view = $this->tpl();

        $id = new \Modl\ItemDAO;
        $cd = new \Modl\ContactDAO;
        $md = new \Modl\MessageDAO;

        $view = $this->tpl();

        $contactr = $cd->getRosterItem($jid);

        $m = $md->getContact($jid, 0, 1);
        if(isset($m)) {
            $view->assign('message', $m[0]);
        }

        $view->assign('mood', getMood());
        $view->assign('clienttype', getClientTypes());
        $view->assign('contact', $cd->get($jid));
        $view->assign('contactr', $contactr);
        $view->assign('subscriptions', $id->getSharedItems($jid));

        if(isset($contactr)) {
            if($contactr->value != null) {
                $view->assign('presence', getPresencesTxt()[$contactr->value]);
            }

            $view->assign('caps', $contactr->getCaps());
        }

        return $view->draw('_contactdata', true);
    }

    public function ajaxRefresh($jid)
    {
        if(!$this->validateJid($jid)) return;

        $cd = new \Modl\ContactDAO;
        $c  = $cd->get($jid, true);

        if($c == null
        || $c->created == null
        || $c->isOld()) {
            if($c == null) {
                $c = new \Modl\Contact;
                $c->jid = $jid;
            }

            $a = new Moxl\Xec\Action\Avatar\Get;
            $a->setTo(echapJid($jid))->request();

            $v = new Moxl\Xec\Action\Vcard\Get;
            $v->setTo(echapJid($jid))->request();

            $r = new Moxl\Xec\Action\Vcard4\Get;
            $r->setTo(echapJid($jid))->request();
        }
    }

    function ajaxAccept($jid)
    {
        $i = new Invitations;
        $i->ajaxAccept($jid);
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

    public function display()
    {
        $this->view->assign('jid', $this->get('s'));
    }
}
