<?php

use Moxl\Xec\Action\Register\ChangePassword;
use Moxl\Xec\Action\Register\Remove;
use Respect\Validation\Validator;

class Account extends WidgetBase
{
    function load()
    {
        $this->addjs('account.js');
        $this->registerEvent('register_changepassword_handle', 'onPasswordChanged');
        $this->registerEvent('register_remove_handle', 'onRemoved');
    }

    function onPasswordChanged()
    {
        RPC::call('Account.resetPassword');
        Notification::append(null, $this->__('account.password_changed'));
    }

    function onRemoved()
    {
        $md = new Modl\MessageDAO;
        $md->clearMessage();
        $pd = new Modl\PostnDAO;
        $pd->deleteNode($this->user->getLogin(), 'urn:xmpp:microblog:0');
        RPC::call('Account.clearAccount');
    }

    function ajaxChangePassword($form)
    {
        $validate = Validator::string()->length(6, 40);
        $p1 = $form->password->value;
        $p2 = $form->password_confirmation->value;

        if($validate->validate($p1)
        && $validate->validate($p2)) {
            if($p1 == $p2) {
                $arr = explodeJid($this->user->getLogin());

                $cp = new ChangePassword;
                $cp->setTo($arr['server'])
                   ->setUsername($arr['username'])
                   ->setPassword($p1)
                   ->request();
            } else {
                RPC::call('Account.resetPassword');
                Notification::append(null, $this->__('account.password_not_same'));
            }
        } else {
            RPC::call('Account.resetPassword');
            Notification::append(null, $this->__('account.password_not_valid'));
        }
    }

    function ajaxRemoveAccount()
    {
        $view = $this->tpl();
        $view->assign('jid', $this->user->getLogin());
        Dialog::fill($view->draw('_account_remove', true));
    }

    function ajaxRemoveAccountConfirm()
    {
        $da = new Remove;
        $da->request();
    }

    function display()
    {
    }
}
