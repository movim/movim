<?php

use Respect\Validation\Validator;

class ContactData extends \Movim\Widget\Base
{
    public function load()
    {
        $this->addjs('contactdata.js');
        $this->addcss('contactdata.css');
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

        $view->assign('message', $this->user->messages()
                                        ->where(function ($query) use ($jid) {
                                            $query->where('jidfrom', $jid)
                                                  ->orWhere('jidto', $jid);
                                        })
                                        ->orderBy('published', 'desc')
                                        ->first());
        $view->assign('subscriptions', \App\Subscription::where('jid', $jid)
            ->where('public', true)->get());
        $view->assign('contact', App\Contact::firstOrNew(['id' => $jid]));
        $view->assign('roster', $this->user->session->contacts()->where('jid', $jid)->first());

        return $view->draw('_contactdata', true);
    }

    public function ajaxRefresh($jid)
    {
        if (!$this->validateJid($jid)) return;

        $contact = \App\Contact::find($jid);

        if (!$contact || $contact->isOld()) {
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
        return ($validate_jid->validate($jid));
    }

    public function display()
    {
        $this->view->assign('jid', $this->get('s'));
    }
}
