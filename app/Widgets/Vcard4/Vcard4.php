<?php

namespace App\Widgets\Vcard4;

use App\User;
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

    public function prepareForm(string $jid): string
    {
        $vcardform = $this->tpl();

        $contact = \App\Contact::firstOrNew(['id' => $jid]);

        $vcardform->assign('me', User::where('id', $this->me->id)->first());
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
        $contact = $this->me->contact;

        $contact->name = null;

        if (Validator::stringType()->notEmpty()->isValid($vcard->name->value)) {
            $contact->name = $vcard->name->value;
            $n = $this->xmpp(new Nickname);
            $n->setNickname($contact->name)
                ->request();
        }

        $contact->date = Validator::date('Y-m-d')->isValid($vcard->date->value)
            ? $vcard->date->value
            : null;

        $contact->fn = $vcard->fn->value;
        $contact->pronouns = $vcard->pronouns->value;

        $contact->url = Validator::url()->notEmpty()->isValid($vcard->url->value)
            ? $vcard->url->value
            : null;

        $contact->adrlocality     = $vcard->locality->value;
        $contact->adrcountry      = $vcard->country->value;

        $contact->email   = Validator::email()->notEmpty()->isValid($vcard->email->value)
            ? $vcard->email->value
            : null;

        $contact->description = trim($vcard->desc->value);

        $contact->save();

        $contact->id = $this->me->id;

        $setVcard = $this->xmpp(new Set);
        $setVcard->setData($contact)->request();
    }

    public function display()
    {
        $this->view->assign('form', $this->prepareForm($this->me->id));
    }
}
