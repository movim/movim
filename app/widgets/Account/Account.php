<?php

use Moxl\Xec\Action\Register\ChangePassword;
use Moxl\Xec\Action\Register\Remove;
use Moxl\Xec\Action\Register\Get;
use Moxl\Xec\Action\Register\Set;
use Respect\Validation\Validator;

class Account extends WidgetBase
{
    function load()
    {
        $this->addjs('account.js');
        $this->registerEvent('register_changepassword_handle', 'onPasswordChanged');
        $this->registerEvent('register_remove_handle', 'onRemoved');
        $this->registerEvent('register_get_handle', 'onRegister', 'account');
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

    function onRegister($package)
    {
        $content = $package->content;

        $view = $this->tpl();

        if(isset($content->x)) {
            $xml = new \XMPPtoForm();
            $form = $xml->getHTML($content->x->asXML());

            $view->assign('form', $form);
            $view->assign('from', $package->from);
            $view->assign('attributes', $content->attributes());
            $view->assign('actions', null);
            if(isset($content->actions)) {
                $view->assign('actions', $content->actions);
            }

            Dialog::fill($view->draw('_account_form', true), true);
        }

    }

    function ajaxChangePassword($form)
    {
        $validate = Validator::stringType()->length(6, 40);
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

    function ajaxGetRegistration($server)
    {
        if(!$this->validateServer($server)) return;

        $da = new Get;
        $da->setTo($server)
           ->request();
    }

    function ajaxRegister($server, $form)
    {
        if(!$this->validateServer($server)) return;
        $s = new Set;
        $s->setTo($server)
          ->setData($form)
          ->request();
    }

    private function validateServer($server)
    {
        $validate_server = Validator::stringType()->noWhitespace()->length(6, 80);
        if(!$validate_server->validate($server)) return false;
        else return true;
    }

    function display()
    {
        $id = new \Modl\ItemDAO;
        $this->view->assign('gateway', $id->getGateways($this->user->getServer()));
    }
}
