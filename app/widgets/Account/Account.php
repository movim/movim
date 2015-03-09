<?php

use Moxl\Xec\Action\Register\ChangePassword;
use Moxl\Xec\Action\Register\Remove;
use Respect\Validation\Validator;

class Account extends WidgetBase
{
    function load()
    {
        $this->registerEvent('register_changepassword_handle', 'onPasswordChanged');
    }

    function onPasswordChanged()
    {
        Notification::append(null, $this->__('account.password_changed'));
    }

    function ajaxChangePassword($form)
    {
        $validate = Validator::string()->length(6, 40);
        $p1 = $form->password->value;
        $p2 = $form->password_confirmation->value;

        if($validate->validate($p1)
        && $validate->validate($p2)) {
            if($p1 == $p2) {
                // TODO send the password 
            } else {
                Notification::append(null, $this->__('account.password_not_same'));
            }
        } else {
            Notification::append(null, $this->__('account.password_not_valid'));
        }
    }

    function ajaxRemoveAccount()
    {
    
    }

    function ajaxRemoveAccountConfirm()
    {

    }

    function display()
    {
    }
}
