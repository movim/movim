<?php

use Moxl\Xec\Action\Vcard4\Get;
use Moxl\Xec\Action\Vcard4\Set;
use Moxl\Xec\Action\Nickname\Set as Nickname;

use Respect\Validation\Validator;

class Vcard4 extends \Movim\Widget\Base
{
    function load()
    {
        $this->registerEvent('vcard4_get_handle', 'onMyVcard4');
        $this->registerEvent('vcard4_set_handle', 'onMyVcard4');
    }

    function display()
    {
        $cd = new \Modl\ContactDAO();
        $me = $cd->get();

        $this->view->assign('getvcard', $this->call('ajaxGetVcard'));

        if($me == null) {
            $this->view->assign('form', $this->prepareForm(new \modl\Contact()));
        } else {
            $this->view->assign('form', $this->prepareForm($me));
        }
    }

    function prepareForm($me) {
        $vcardform = $this->tpl();

        $vcardform->assign('me',       $me);
        $vcardform->assign('desc',     trim($me->description));
        $vcardform->assign('gender',   getGender());
        $vcardform->assign('marital',  getMarital());
        $vcardform->assign('countries',getCountries());

        $vcardform->assign(
            'submit',
            $this->call('ajaxVcardSubmit', "MovimUtils.formToJson('vcard4')")
            );

        $vcardform->assign(
            'privacy',
            $this->call('ajaxChangePrivacy', "this.checked")
            );

        // The datepicker arrays
        $days = $months = $years = [];
        for($i=1; $i<= 31; $i++) {
            if($i < 10){
                $j = '0'.$i;
            } else {
                $j = (string)$i;
            }
            $days[$i] = $j;
        }
        for($i=1; $i<= 12; $i++) {
            if($i < 10){
                $j = '0'.$i;
            } else {
                $j = (string)$i;
            }
            $m = getMonths();

            $months[$j] = $m[$i];
        }
        for($i=date('o'); $i>= 1920; $i--) { array_push($years, $i); }

        $vcardform->assign('days',   $days);
        $vcardform->assign('months', $months);
        $vcardform->assign('years',  $years);

        return $vcardform->draw('_vcard4_form', true);
    }

    function onMyVcard4($packet) {
        $c = $packet->content;
        $html = $this->prepareForm($c);

        Notification::append(null, $this->__('vcard.updated'));

        RPC::call('movim_fill', 'vcard_form', $html);
        RPC::commit();
    }

    function onMyVcard4Received() {
        RPC::call('MovimUtils.buttonReset', '#vcard4validate');
        Notification::append(null, $this->__('vcard.updated'));
        RPC::commit();
    }

    function onMyVcard4NotReceived() {
        Notification::append(null, $this->__('vcard.not_updated'));
        RPC::commit();
    }

    function ajaxGetVcard() {
        $r = new Get;
        $r->setTo($this->user->getLogin())
          ->setMe()
          ->request();
    }

    function ajaxVcardSubmit($vcard) {
        # Format it ISO 8601:
        if($vcard->year->value  != -1
        && $vcard->month->value != -1
        && $vcard->day->value   != -1)
            $vcard->date->value =
                    $vcard->year->value.'-'.
                    $vcard->month->value.'-'.
                    $vcard->day->value;

        unset($vcard->year->value);
        unset($vcard->month->value);
        unset($vcard->day->value);

        $cd = new \Modl\ContactDAO();
        $c = $cd->get($this->user->getLogin());

        if($c == null)
            $c = new \Modl\Contact();

        $c->jid     = $this->user->getLogin();

        if(isset($vcard->date->value)) {
            $c->date = $vcard->date->value;
        }

        if(Validator::stringType()->length(0, 40)->validate($vcard->name->value)) {
            $c->name    = $vcard->name->value;
            $n = new Nickname;
            $n->setNickname($c->name)
              ->request();
        }

        if(Validator::stringType()->length(0, 40)->validate($vcard->fn->value))
            $c->fn      = $vcard->fn->value;

        if(Validator::url()->validate($vcard->url->value))
            $c->url     = $vcard->url->value;

        if(Validator::in(array_keys(getGender()))->validate($vcard->gender->value))
            $c->gender  = $vcard->gender->value;
        if(Validator::in(array_keys(getMarital()))->validate($vcard->marital->value))
            $c->marital = $vcard->marital->value;

        $c->adrlocality     = $vcard->locality->value;
        $c->adrcountry      = $vcard->country->value;

        if(Validator::email()->validate($vcard->email->value))
            $c->email   = $vcard->email->value;

        $c->twitter = $vcard->twitter->value;
        $c->skype   = $vcard->skype->value;
        $c->yahoo   = $vcard->yahoo->value;

        if(Validator::stringType()->validate($vcard->desc->value))
            $c->description     = trim($vcard->desc->value);

        $cd = new \Modl\ContactDAO();
        $cd->set($c);

        $r = new Set;
        $r->setData($c)->request();

        $r = new Moxl\Xec\Action\Vcard\Set;
        $r->setData($vcard)->request();
    }

    function ajaxChangePrivacy($value) {
        if($value == true) {
            \modl\Privacy::set($this->user->getLogin(), 1);
            Notification::append(null, $this->__('vcard.public'));
        } else {
            \modl\Privacy::set($this->user->getLogin(), 0);
            Notification::append(null, $this->__('vcard.restricted'));
        }
    }
}
