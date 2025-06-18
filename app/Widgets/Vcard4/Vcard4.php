<?php

namespace App\Widgets\Vcard4;

use App\Widgets\Toast\Toast;
use Movim\Widget\Base;

use Moxl\Xec\Action\Vcard4\Get;
use Moxl\Xec\Action\Vcard4\Set;
use Moxl\Xec\Action\Nickname\Set as Nickname;

use Respect\Validation\Validator;

class Vcard4 extends Base
{
    public function load()
    {
        $this->registerEvent('vcard4_get_handle', 'onMyVcard4');
        $this->registerEvent('vcard4_set_handle', 'onMyVcard4');
    }

    public function prepareForm($contact)
    {
        $vcardform = $this->tpl();

        $vcardform->assign('me', $this->user);
        $vcardform->assign('contact', $contact);
        $vcardform->assign('desc', trim($contact->description ?? ''));
        $vcardform->assign('countries', getCountries());

        $contact->isValidDate();

        return $vcardform->draw('_vcard4_form');
    }

    public function onMyVcard4($packet)
    {
        $html = $this->prepareForm($packet->content);

        Toast::send($this->__('vcard.updated'));

        $this->rpc('MovimTpl.fill', '#vcard_form', $html);
        $this->rpc('MovimUtils.applyAutoheight');
    }

    public function onMyVcard4Received()
    {
        Toast::send($this->__('vcard.updated'));
    }

    public function onMyVcard4NotReceived()
    {
        Toast::send($this->__('vcard.not_updated'));
    }

    public function ajaxGetVcard()
    {
        $r = new Get;
        $r->setTo($this->user->id)
            ->request();
    }

    public function ajaxVcardSubmit($vcard)
    {
        $c = $this->user->contact;

        $c->name = null;

        if (Validator::stringType()->notEmpty()->isValid($vcard->name->value)) {
            $c->name = $vcard->name->value;
            $n = new Nickname;
            $n->setNickname($c->name)
                ->request();
        }

        $c->date = Validator::date('Y-m-d')->isValid($vcard->date->value)
            ? $vcard->date->value
            : null;

        $c->fn = $vcard->fn->value;

        $c->url = Validator::url()->notEmpty()->isValid($vcard->url->value)
            ? $vcard->url->value
            : null;

        $c->adrlocality     = $vcard->locality->value;
        $c->adrcountry      = $vcard->country->value;

        $c->email   = Validator::email()->notEmpty()->isValid($vcard->email->value)
            ? $vcard->email->value
            : null;

        $c->description     = trim($vcard->desc->value);

        $c->save();

        $c->jid = $this->user->id;

        $r = new Set;
        $r->setData($c)->request();
    }

    public function display()
    {
        $this->view->assign('form', $this->prepareForm($this->user->contact));
    }
}
