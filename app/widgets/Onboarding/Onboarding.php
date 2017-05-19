<?php

class Onboarding extends \Movim\Widget\Base
{
    function load()
    {
        $this->addcss('onboarding.css');
        $this->addjs('onboarding.js');
    }

    public function ajaxAskNotifications()
    {
        $tpl = $this->tpl();
        $this->rpc('Onboarding.setNotifications');
        Dialog::fill($tpl->draw('_onboarding_notifications', true));
    }

    public function ajaxAskPublic()
    {
        $tpl = $this->tpl();
        $this->rpc('Onboarding.setPublic');
        Dialog::fill($tpl->draw('_onboarding_public', true));
    }

    public function ajaxAskPopups()
    {
        $tpl = $this->tpl();
        Dialog::fill($tpl->draw('_onboarding_popups', true));
        $this->rpc('Onboarding.setPopups');
    }

    public function ajaxEnablePublic()
    {
        \Modl\Privacy::set($this->user->getLogin(), 1);
        Notification::append(null, $this->__('vcard.public'));
    }
}
