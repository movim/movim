<?php

namespace App\Widgets\Register;

use Movim\Librairies\XMPPtoForm;
use Moxl\Xec\Action\Register\Set;
use Moxl\Xec\Action\Register\Get;
use Moxl\Xec\Payload\Packet;

class Register extends \Movim\Widget\Base
{
    public function load()
    {
        $this->addjs('register.js');
        $this->addcss('register.css');

        $this->registerEvent('register_get_handle', 'onForm');
        $this->registerEvent('register_set_handle', 'onRegistered');
        $this->registerEvent('register_set_errorconflict', 'onRegisterError', 'register');
        $this->registerEvent('register_set_errorforbidden', 'onForbidden', 'register');
        $this->registerEvent('register_set_errornotacceptable', 'onRegisterNotAcceptable', 'register');
        $this->registerEvent('register_get_errorserviceunavailable', 'onServiceUnavailable', 'register');
    }

    public function onForm(Packet $packet)
    {
        $form = $packet->content;

        $xtf = new XMPPtoForm;
        $html = '';
        if (!empty($form->x)) {
            switch ($form->x->attributes()->xmlns) {
                case 'jabber:x:data':
                    $formview = $this->tpl();
                    $formview->assign('formh', $xtf->getHTML($form->x, $form));
                    $html = $formview->draw('_register_form');
                    break;
                case 'jabber:x:oob':
                    $this->rpc('MovimUtils.redirect', (string)$form->x->url);
                    break;
            }
        } else {
            $formview = $this->tpl();
            $formview->assign('formh', $xtf->getHTML($form));
            $html = $formview->draw('_register_form');
        }

        $this->rpc('MovimTpl.fill', '#subscription_form', $html);
    }

    public function onRegistered(Packet $packet)
    {
        $view = $this->tpl();
        $this->rpc('MovimTpl.fill', '#subscribe', $view->draw('_register_registered'));
    }

    public function onError()
    {
        $this->toast($this->__('error.service_unavailable'));
    }

    public function onRegisterError(Packet $packet)
    {
        $error = $packet->content;
        $this->toast($error);
    }

    public function onForbidden()
    {
        $this->toast($this->__('error.forbidden'));
    }

    public function onRegisterNotAcceptable()
    {
        $this->toast($this->__('error.not_acceptable'));
    }

    public function onServiceUnavailable()
    {
        $this->toast($this->__('error.service_unavailable'));

        requestAPI('disconnect', post: ['sid' => $this->me->session->id]);

        $this->rpc('MovimUtils.redirect', $this->route('account'));
    }

    public function ajaxGetForm(string $host)
    {
        $this->rpc('register', $host);

        linker($this->sessionId)->writeXMPP(\Moxl\Stanza\Stream::init($host));

        $g = $this->xmpp(new Get);
        $g->setTo($host)->request();
    }

    public function ajaxRegister($form)
    {
        if (
            isset($form->re_password)
            && $form->re_password->value != $form->password->value
        ) {
            $this->toast($this->__('account.password_not_same'));
            return;
        }

        $s = $this->xmpp(new Set);
        $s->setData(formToArray($form))
            ->request();
    }

    public function display()
    {
        $host = $this->get('s');
        $this->view->assign('host', $host);
    }
}
