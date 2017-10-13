<?php

use Moxl\Xec\Action\Storage\Get;

use Respect\Validation\Validator;
use Defuse\Crypto\Key;
use Defuse\Crypto\Crypto;

use Movim\Cookie;
use Movim\Session;

class Login extends \Movim\Widget\Base
{
    function load()
    {
        $this->addcss('login.css');
        $this->addjs('login.js');
        $this->registerEvent('session_start_handle', 'onStart');
        $this->registerEvent('saslfailure', 'onSASLFailure');
        $this->registerEvent('storage_get_handle', 'onConfig');
        $this->registerEvent('storage_get_errorfeaturenotimplemented', 'onConfig');
        $this->registerEvent('storage_get_errorserviceunavailable', 'onConfig');
        $this->registerEvent('ssl_error', 'onSSLError');
    }

    function onStart($packet)
    {
        $session = Session::start();

        if($session->get('mechanism') != 'ANONYMOUS') {
            // We get the configuration
            $s = new Get;
            $s->setXmlns('movim:prefs')
              ->request();
        }
    }

    function onConfig($packet)
    {
        $this->user->createDir();
        $this->rpc('MovimUtils.reloadThis');

        $p = new Presence;
        $p->start();
    }

    function display()
    {
        $cd = new \Modl\ConfigDAO;
        $config = $cd->get();

        $this->view->assign('info',     $config->info);
        $this->view->assign('whitelist',$config->xmppwhitelist);

        if(isset($config->xmppdomain)
        && !empty($config->xmppdomain)) {
            $this->view->assign('domain', $config->xmppdomain);
        } else {
            $this->view->assign('domain', 'movim.eu');
        }

        $pop = 0;

        foreach(scandir(USERS_PATH) as $f) {
            if(is_dir(USERS_PATH.'/'.$f)) {
                $pop++;
            }
        }

        $this->view->assign('invitation', null);

        if($this->get('i') && Validator::length(8)->validate($this->get('i'))) {
            $invitation = \Modl\Invite::get($this->get('i'));
            $this->view->assign('invitation', $invitation);

            $cd = new \Modl\ContactDAO;
            $this->view->assign('contact', $cd->get($invitation->jid));
        }

        $this->view->assign('pop', $pop-2);
        $this->view->assign('connected', (int)requestURL('http://localhost:1560/started/', 2));
        $this->view->assign('error', $this->prepareError());

        if(isset($_SERVER['PHP_AUTH_USER'])
        && isset($_SERVER['PHP_AUTH_PW'])
        && Validator::email()->length(6, 40)->validate($_SERVER['HTTP_EMAIL'])) {
            list($username, $host) = explode('@', $_SERVER['HTTP_EMAIL']);
            $this->view->assign('httpAuthHost', $host);
            $this->view->assign('httpAuthUser', $_SERVER['HTTP_EMAIL']);
            $this->view->assign('httpAuthPassword', $_SERVER['PHP_AUTH_PW']);
        }
    }

    function showErrorBlock($error)
    {
        $ed = new \Modl\EncryptedPassDAO;
        $ed->delete();

        $this->rpc('Login.clearQuick');
        $this->rpc('MovimTpl.fill', '#error', $this->prepareError($error));
        $this->rpc('MovimUtils.addClass', '#login_widget', 'error');
    }

    function prepareError($error = 'default')
    {
        $view = $this->tpl();

        $key = 'error.'.$error;
        $error_text = $this->__($key);

        if($error_text == $key) {
            $view->assign('error', $this->__('error.default'));
        } else {
            $view->assign('error', $error_text);
        }

        return $view->draw('_login_error', true);
    }

    function onSSLError()
    {
        $this->showErrorBlock('fail_auth');
    }

    function onSASLFailure($packet)
    {
        switch($packet->content) {
            case 'invalid-mechanism':
            case 'malformed-request':
                $error = 'mechanism';
                break;
            case 'not-authorized':
            case 'bad-auth':
                $error = 'wrong_account';
                break;
            case 'bad-protocol':
            default :
                $error = 'fail_auth';
                break;
        }

        $this->showErrorBlock($error);
    }

    function ajaxLogin($form)
    {
        $username = strtolower($form->username->value);
        $password = $form->password->value;

        $this->doLogin($username, $password);
    }

    function ajaxHTTPLogin($login, $password)
    {
        $this->doLogin($login, $password);
    }

    function ajaxQuickLogin($deviceId, $login, $key)
    {
        $validate_login = Validator::stringType()->length(1, 254);

        if(!$validate_login->validate($login)) {
            $this->showErrorBlock('login_format');
            return;
        }

        $db = \Modl\Modl::getInstance();
        $db->setUser($login);

        try {
            $key = Key::loadFromAsciiSafeString($key);

            $ed = new \Modl\EncryptedPassDAO;
            $ciphertext = $ed->get($deviceId);

            if($ciphertext) {
                $password = Crypto::decrypt($ciphertext->data, $key);
                $this->doLogin($login, $password, $deviceId);
            }
        } catch(Exception $e) {
            $this->rpc('Login.clearQuick');
        }
    }

    private function doLogin($login, $password, $deviceId = false)
    {
        // We get the Server Configuration
        $cd = new \Modl\ConfigDAO;
        $config = $cd->get();

        // First we check the form
        $validate_login   = Validator::stringType()->length(1, 254);
        $validate_password = Validator::stringType()->length(1, 128);

        if(!$validate_login->validate($login)) {
            $this->showErrorBlock('login_format');
            return;
        }

        if(!$validate_password->validate($password)) {
            $this->showErrorBlock('password_format');
            return;
        }

        $db = \Modl\Modl::getInstance();
        $db->setUser($login);

        list($username, $host) = explode('@', $login);

        // Check whitelisted server
        if(
            $config->xmppwhitelist != '' &&!
            in_array(
                $host,
                explode(',',$config->xmppwhitelist)
                )
            ) {
            $this->showErrorBlock('unauthorized');
            return;
        }

        // We check if we already have an open session
        $sd = new \Modl\SessionxDAO;
        $here = $sd->getHash(sha1($username.$password.$host));

        $rkey = Key::createNewRandomKey();

        $ed = new \Modl\EncryptedPassDAO;

        $deviceId = generateKey(16);
        $ciphertext = Crypto::encrypt($password, $rkey);

        $key = new \Modl\EncryptedPass;
        $key->id = $deviceId;
        $key->data = $ciphertext;

        $ed->set($key);

        $this->rpc('Login.setQuick', $deviceId, $login, $host, $rkey->saveToAsciiSafeString());

        if($here) {
            $this->rpc('Login.setCookie', 'MOVIM_SESSION_ID', $here->session, date(DATE_COOKIE, Cookie::getTime()));
            $this->rpc('MovimUtils.redirect', $this->route('main'));
            return;
        }

        $s = new \Modl\Sessionx;
        $s->init($username, $password, $host);
        $s->loadMemory();
        $sd->set($s);

        // We launch the XMPP socket
        $this->rpc('register', $host);

        \Moxl\Stanza\Stream::init($host);
    }
}
