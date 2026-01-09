<?php

namespace App\Widgets\AccountNext;

use Movim\Librairies\XMPPtoForm;
use Moxl\Xec\Action\Register\Set;
use Moxl\Xec\Action\Register\Get;
use Moxl\Xec\Payload\Packet;

class AccountNext extends \Movim\Widget\Base
{
    public function load()
    {
        $this->addjs('accountnext.js');
        $this->addcss('accountnext.css');

        $this->registerEvent('register_get_handle', 'onForm');
        $this->registerEvent('register_set_handle', 'onRegistered');
        $this->registerEvent('register_set_errorconflict', 'onRegisterError', 'accountnext');
        $this->registerEvent('register_set_errorforbidden', 'onForbidden', 'accountnext');
        $this->registerEvent('register_set_errornotacceptable', 'onRegisterNotAcceptable', 'accountnext');
        $this->registerEvent('register_get_errorserviceunavailable', 'onServiceUnavailable', 'accountnext');
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
                    $html = $formview->draw('_accountnext_form');
                    break;
                case 'jabber:x:oob':
                    $this->rpc('MovimUtils.redirect', (string)$form->x->url);
                    break;
            }
        } else {
            $formview = $this->tpl();
            $formview->assign('formh', $xtf->getHTML($form));
            $html = $formview->draw('_accountnext_form');
        }

        $this->rpc('MovimTpl.fill', '#subscription_form', $html);
    }

    public function onRegistered(Packet $packet)
    {
        $view = $this->tpl();
        $this->rpc('MovimTpl.fill', '#subscribe', $view->draw('_accountnext_registered'));
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
