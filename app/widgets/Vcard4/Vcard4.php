<?php

use Moxl\Xec\Action\Vcard4\Get;
use Moxl\Xec\Action\Vcard4\Set;
use Moxl\Xec\Action\Nickname\Set as Nickname;

use Respect\Validation\Validator;
use App\User;

class Vcard4 extends \Movim\Widget\Base
{
    function load()
    {
        $this->registerEvent('vcard4_get_handle', 'onMyVcard4');
        $this->registerEvent('vcard4_set_handle', 'onMyVcard4');
    }

    function prepareForm($contact)
    {
        $vcardform = $this->tpl();

        $vcardform->assign('me',       User::me());
        $vcardform->assign('contact',  $contact);
        $vcardform->assign('desc',     trim($contact->description));
        $vcardform->assign('countries',getCountries());

        $contact->isValidDate();

        $vcardform->assign(
            'submit',
            $this->call('ajaxVcardSubmit', "MovimUtils.formToJson('vcard4')")
            );

        $vcardform->assign(
            'privacy',
            $this->call('ajaxChangePrivacy', "this.checked")
            );

        return $vcardform->draw('_vcard4_form', true);
    }

    function onMyVcard4($packet)
    {
        $html = $this->prepareForm($packet->content);

        Notification::append(null, $this->__('vcard.updated'));

        $this->rpc('MovimTpl.fill', '#vcard_form', $html);
    }

    function onMyVcard4Received()
    {
        Notification::append(null, $this->__('vcard.updated'));
    }

    function onMyVcard4NotReceived()
    {
        Notification::append(null, $this->__('vcard.not_updated'));
    }

    function ajaxGetVcard()
    {
        $r = new Get;
        $r->setTo($this->dbuser->id)
          ->setMe()
          ->request();
    }

    function ajaxVcardSubmit($vcard)
    {
        $c = User::me()->contact;

        if (Validator::stringType()->length(0, 40)->validate($vcard->name->value)) {
            $c->name    = $vcard->name->value;
            $n = new Nickname;
            $n->setNickname($c->name)
              ->request();
        }

        if (Validator::date('d-m-Y')->validate($vcard->date->value)) {
            $c->date    = $vcard->date->value;
        }

        if (Validator::stringType()->length(0, 40)->validate($vcard->fn->value)) {
            $c->fn      = $vcard->fn->value;
        }

        if (Validator::url()->validate($vcard->url->value)) {
            $c->url     = $vcard->url->value;
        } else {
            $c->url     = '';
        }

        $c->adrlocality     = $vcard->locality->value;
        $c->adrcountry      = $vcard->country->value;

        if (Validator::email()->validate($vcard->email->value)) {
            $c->email   = $vcard->email->value;
        } else {
            $c->email = '';
        }

        if (Validator::stringType()->validate($vcard->desc->value)) {
            $c->description     = trim($vcard->desc->value);
        }

        $c->save();

        $r = new Set;
        $r->setData($c)->request();

        $r = new Moxl\Xec\Action\Vcard\Set;
        $r->setData($vcard)->request();
    }

    function ajaxChangePrivacy($value)
    {
        if ($value == true) {
            $this->user->dbuser->setPublic();
            Notification::append(null, $this->__('vcard.public'));
        } else {
            $this->user->dbuser->setPrivate();
            Notification::append(null, $this->__('vcard.restricted'));
        }
    }

    function display()
    {
        $this->view->assign('getvcard', $this->call('ajaxGetVcard'));
        $this->view->assign('form', $this->prepareForm(User::me()));
    }
}
