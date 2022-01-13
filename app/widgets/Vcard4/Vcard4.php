<?php

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

        if (Validator::stringType()->length(0, 40)->validate($vcard->name->value)) {
            $c->name    = $vcard->name->value;
            $n = new Nickname;
            $n->setNickname($c->name)
              ->request();
        }

        if (Validator::date('Y-m-d')->validate($vcard->date->value)) {
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

    public function ajaxEditNickname()
    {
        $view = $this->tpl();
        $view->assign('me', $this->user);
        Dialog::fill($view->draw('_vcard4_nickname'));
    }

    public function ajaxSaveNickname(string $nickname)
    {
        if (Validator::regex('/^[a-z_\-\d]{3,64}$/i')->validate($nickname)) {
            if (\App\User::where('nickname', $nickname)->where('id', '!=', $this->user->id)->first()) {
                Toast::send($this->__('profile.nickname_conflict'));
                return;
            }

            $this->user->nickname = $nickname;
            $this->user->save();
            $this->ajaxGetVcard();

            (new Dialog)->ajaxClear();
            Toast::send($this->__('profile.nickname_saved'));
        } else {
            Toast::send($this->__('profile.nickname_error'));
        }
    }

    public function ajaxChangePrivacy($value)
    {
        if ($value == true) {
            $this->user->setPublic();
            Toast::send($this->__('vcard.public'));
        } else {
            $this->user->setPrivate();
            Toast::send($this->__('vcard.restricted'));
        }
    }

    public function display()
    {
        $this->view->assign('form', $this->prepareForm($this->user->contact));
    }
}
