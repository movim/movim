<?php

use Moxl\Xec\Action\Disco\Request;
use Moxl\Xec\Action\Register\Get;
use Moxl\Xec\Action\Register\Set;

class AccountNext extends WidgetBase {
    function load()
    {
        $this->addjs('accountnext.js');
        $this->addcss('accountnext.css');

        $this->registerEvent('register_get_handle', 'onForm');
        $this->registerEvent('register_set_handle', 'onRegistered');
        $this->registerEvent('register_set_errorconflict', 'onRegisterError', 'accountnext');
        $this->registerEvent('register_set_errornotacceptable', 'onRegisterNotAcceptable', 'accountnext');
        $this->registerEvent('register_get_errorserviceunavailable', 'onServiceUnavailable', 'accountnext');
    }

    function display()
    {
        $host = $this->get('s');

        $this->view->assign('init', $this->call('ajaxInit', "'".$host."'"));
        $this->view->assign('getsubscriptionform', $this->call('ajaxGetForm', "'".$host."'"));
        $this->view->assign('host', $host);
    }

    function onForm($package)
    {
        $form = $package->content;

        $xtf = new \XMPPtoForm();
        if(!empty($form->x)){
            switch($form->x->attributes()->xmlns) {
                case 'jabber:x:data' :
                    $formview = $this->tpl();

                    $formh = $xtf->getHTML($form->x->asXML());
                    $formview->assign('submitdata', $this->call('ajaxRegister', "movim_form_to_json('data')"));

                    $formview->assign('formh', $formh);
                    $html = $formview->draw('_accountnext_form', true);

                    RPC::call('movim_fill', 'subscription_form', $html);
                    break;
                case 'jabber:x:oob' :
                    $oobview = $this->tpl();
                    $oobview->assign('url', (string)$form->x->url);

                    $html = $oobview->draw('_accountnext_oob', true);

                    RPC::call('movim_fill', 'subscription_form', $html);
                    break;
            }

        } else{
            $formh = $xtf->getHTML($form->asXML());
        }
    }

    function onRegistered($packet)
    {
        $data = $packet->content;

        $view = $this->tpl();
        $view->assign('url', (string)$form->x->url);

        $html = $view->draw('_accountnext_registered', true);

        RPC::call('movim_fill', 'subscription_form', $html);
        RPC::call('setUsername', $data->username->value);
    }

    function onRegisterError($package)
    {
        $error = $package->content;
        Notification::append(null, $error);
    }

    function onRegisterNotAcceptable()
    {
        Notification::append(null, $this->__('error.not_acceptable'));
    }

    function onServiceUnavailable()
    {
        Notification::append(null, $this->__('error.service_unavailable'));
        RPC::call('remoteUnregister');
        RPC::call('movim_redirect', $this->route('account'));
    }

    function ajaxGetForm($host)
    {
        $domain = \Moxl\Utils::getDomain($host);

        // We create a new session or clear the old one
        $s = Sessionx::start();
        $s->init(null, null, $host, $domain);

        \Moxl\Stanza\Stream::init($host);
    }

    function ajaxRegister($form)
    {
        $s = new Set;
        $s->setData($form)->request();
    }
}
