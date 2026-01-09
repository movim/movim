<?php

namespace App\Widgets\Vcard4;

use Movim\Widget\Base;

use Moxl\Xec\Action\Vcard4\Get;
use Moxl\Xec\Action\Vcard4\Set;
use Moxl\Xec\Action\Nickname\Set as Nickname;
use Moxl\Xec\Payload\Packet;
use Respect\Validation\Validator;

class Vcard4 extends Base
{
    public function load()
    {
        $this->registerEvent('vcard4_get_handle', 'onMyVcard4', 'configuration');
        $this->registerEvent('vcard4_set_handle', 'onMyVcard4', 'configuration');
    }

    public function prepareForm(string $jid)
    {
        $vcardform = $this->tpl();

        $contact = \App\Contact::firstOrNew(['id' => $jid]);

        $vcardform->assign('me', $this->me);
        $vcardform->assign('contact', $contact);
        $vcardform->assign('desc', trim($contact->description ?? ''));
        $vcardform->assign('countries', getCountries());

        $contact->isValidDate();

        return $vcardform->draw('_vcard4_form');
    }

    public function onMyVcard4(Packet $packet)
    {
        $html = $this->prepareForm($packet->content);

        $this->toast($this->__('vcard.updated'));

        $this->rpc('MovimTpl.fill', '#vcard_form', $html);
        $this->rpc('MovimUtils.applyAutoheight');
    }

    public function onMyVcard4Received()
    {
        $this->toast($this->__('vcard.updated'));
    }

    public function onMyVcard4NotReceived()
    {
        $this->toast($this->__('vcard.not_updated'));
    }

    public function ajaxGetVcard()
    {
        $r = $this->xmpp(new Get);
        $r->setTo($this->me->id)
            ->request();
    }

    public function ajaxVcardSubmit($vcard)
    {
        $c = $this->me->contact;

        $c->name = null;

        if (Validator::stringType()->notEmpty()->isValid($vcard->name->value)) {
            $c->name = $vcard->name->value;
            $n = $this->xmpp(new Nickname);
            $n->setNickname($c->name)
                ->request();
        }

        $c->date = Validator::date('Y-m-d')->isValid($vcard->date->value)
            ? $vcard->date->value
            : null;

        $c->fn = $vcard->fn->value;
        $c->pronouns = $vcard->pronouns->value;

        $c->url = Validator::url()->notEmpty()->isValid($vcard->url->value)
            ? $vcard->url->value
            : null;

        $c->adrlocality     = $vcard->locality->value;
        $c->adrcountry      = $vcard->country->value;

        $c->email   = Validator::email()->notEmpty()->isValid($vcard->email->value)
            ? $vcard->email->value
            : null;

        $c->description = trim($vcard->desc->value);

        $c->save();

        $c->id = $this->me->id;

        $r = $this->xmpp(new Set);
        $r->setData($c)->request();
    }

    public function display()
    {
        $this->view->assign('form', $this->prepareForm($this->me->id));
    }
}
