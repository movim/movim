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
        Dialog::fill($tpl->draw('_onboarding_notifications', true));
    }

    public function ajaxAskPublic()
    {
        $tpl = $this->tpl();
        Dialog::fill($tpl->draw('_onboarding_public', true));
    }

    public function ajaxEnablePublic()
    {
        \Modl\Privacy::set($this->user->getLogin(), 1);
        $this->rpc('Onboarding.setPublic');
        Notification::append(null, $this->__('vcard.public'));
    }
}
