<?php

namespace App\Widgets\Onboarding;

use Movim\Widget\Base;
use App\Widgets\Dialog\Dialog;

class Onboarding extends Base
{
    public function load()
    {
        $this->addcss('onboarding.css');
        $this->addjs('onboarding.js');
    }

    public function ajaxAskPublic()
    {
        $tpl = $this->tpl();
        $this->rpc('Onboarding.setPublic');

        if ($this->me->public === null) {
            Dialog::fill($tpl->draw('_onboarding_public'));
        }
    }

    public function ajaxEnablePublic()
    {
        $this->me->setPublic();
        $this->toast($this->__('profile.public'));
    }

    public function ajaxEnableRestricted()
    {
        $this->me->setPrivate();
        $this->toast($this->__('profile.restricted'));
    }
}
