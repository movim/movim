<?php

use Moxl\Xec\Action\Storage\Get;

use Respect\Validation\Validator;
use Defuse\Crypto\Key;
use Defuse\Crypto\Crypto;
use League\CommonMark\CommonMarkConverter;

use App\Configuration;
use App\User;

use Movim\Widget\Base;

use Movim\Cookie;

class Login extends Base
{
    public function load()
    {
        $this->addcss('login.css');
        $this->addjs('login.js');
        $this->registerEvent('session_start_handle', 'onStart');
        $this->registerEvent('saslsuccess', 'onSASLSuccess');
        $this->registerEvent('saslfailure', 'onSASLFailure');
        $this->registerEvent('socket_connected', 'onConnected');
        $this->registerEvent('storage_get_handle', 'onConfig');
        $this->registerEvent('storage_get_error', 'onConfig');
        $this->registerEvent('ssl_error', 'onFailAuth');
        $this->registerEvent('dns_error', 'onDNSError');
        $this->registerEvent('timeout_error', 'onTimeoutError');
        $this->registerEvent('streamerror', 'onFailAuth');
    }

    public function onStart($packet)
    {
        //$session = Session::start();

        //if ($session->get('mechanism') != 'ANONYMOUS') {
        // We get the configuration
        $s = new Get;
        $s->setXmlns('movim:prefs')
              ->request();
        //}
    }

    public function onConnected($packet)
    {
        Toast::send($this->__('connection.socket_connected'));
    }

    public function onSASLSuccess($packet)
    {
        Toast::send($this->__('connection.authenticated'));
    }

    public function onConfig($packet)
    {
        $p = new Presence;
        $p->start();

        $this->rpc('MovimUtils.reloadThis');
    }

    public function display()
    {
        $configuration = Configuration::get();

        if (!empty($configuration->info)) {
            $converter = new CommonMarkConverter([
                'html_input' => 'strip',
                'allow_unsafe_links' => false,
            ]);

            $this->view->assign('info', $converter->convert($configuration->info));
        }

        $this->view->assign('banner', $configuration->banner);
        $this->view->assign('whitelist', $configuration->xmppwhitelist);

        if (isset($configuration->xmppdomain)
        && !empty($configuration->xmppdomain)) {
            $this->view->assign('domain', $configuration->xmppdomain);
        } else {
            $this->view->assign('domain', 'movim.eu');
        }

        $this->view->assign('invitation', null);

        if ($this->get('i')
        && Validator::length(8)->validate($this->get('i'))) {
            $invitation = \App\Invite::find($this->get('i'));

            if ($invitation) {
                $this->view->assign('invitation', $invitation);
                $this->view->assign('contact', \App\Contact::firstOrNew(['id' => $invitation->user_id]));
            }
        }

        $this->view->assign('pop', User::count());
        $this->view->assign('admins', User::where('admin', true)->get());
        $this->view->assign('connected', (int)requestAPI('started', 2));
        $this->view->assign('error', $this->prepareError());

        if (isset($_SERVER['PHP_AUTH_USER'])
        && isset($_SERVER['PHP_AUTH_PW'])
        && Validator::email()->length(6, 40)->validate($_SERVER['HTTP_EMAIL'])) {
            list($username, $host) = explode('@', $_SERVER['HTTP_EMAIL']);
            $this->view->assign('httpAuthHost', $host);
            $this->view->assign('httpAuthUser', $_SERVER['HTTP_EMAIL']);
            $this->view->assign('httpAuthPassword', $_SERVER['PHP_AUTH_PW']);
        }
    }

    public function showErrorBlock($error)
    {
        $this->user->encryptedPasswords()->delete();

        $this->rpc('Login.clearQuick');
        $this->rpc('MovimTpl.fill', '#error', $this->prepareError($error));
        $this->rpc('MovimUtils.addClass', '#login_widget', 'error');
    }

    public function prepareError($error = 'default')
    {
        $view = $this->tpl();

        $key = 'error.'.$error;
        $error_text = $this->__($key);

        if ($error_text == $key) {
            $view->assign('error', $this->__('error.default'));
        } else {
            $view->assign('error', $error_text);
        }

        return $view->draw('_login_error');
    }

    public function onFailAuth()
    {
        $this->showErrorBlock('fail_auth');
    }

    public function onDNSError()
    {
        $this->showErrorBlock('dns');
    }

    public function onTimeoutError()
    {
        $this->showErrorBlock('timeout');
    }

    public function onSASLFailure($packet)
    {
        switch ($packet->content) {
            case 'invalid-mechanism':
            case 'malformed-request':
                $error = 'mechanism';
                break;
            case 'not-authorized':
            case 'bad-auth':
                $error = 'wrong_account';
                break;
            case 'bad-protocol':
            default:
                $error = 'fail_auth';
                break;
        }

        $this->showErrorBlock($error);
    }

    public function ajaxLogin($form)
    {
        $username = strtolower($form->username->value);
        $password = $form->password->value;

        $this->doLogin($username, $password);
    }

    public function ajaxHTTPLogin($login, $password)
    {
        $this->doLogin($login, $password);
    }

    public function ajaxQuickLogin($deviceId, $login, $key, $check = false)
    {
        $validate_login = Validator::stringType()->length(1, 254);

        if (!$validate_login->validate($login)) {
            $this->showErrorBlock('login_format');
            return;
        }

        try {
            $key = Key::loadFromAsciiSafeString($key);

            $user = \App\User::find($login);

            if ($user) {
                $ciphertext = $user->encryptedPasswords()->find($deviceId);

                if ($ciphertext) {
                    if ($check) {
                        $this->rpc('Login.quickLoginRegister');
                        return;
                    }

                    $ciphertext->touch();
                    $password = Crypto::decrypt($ciphertext->data, $key);
                    $this->doLogin($login, $password, $deviceId);
                } else {
                    $this->rpc('Login.clearQuick');
                }
            }
        } catch (Exception $e) {
            $this->rpc('Login.clearQuick');
        }
    }

    private function doLogin($login, $password, $deviceId = false)
    {
        // We get the Server Configuration
        $configuration = Configuration::get();

        // First we check the form
        $validate_login   = Validator::stringType()->length(1, 254);
        $validate_password = Validator::stringType()->length(1, 128);

        if (!$validate_login->validate($login)) {
            $this->showErrorBlock('login_format');
            return;
        }

        if (!$validate_password->validate($password)) {
            $this->showErrorBlock('password_format');
            return;
        }

        list($username, $host) = explode('@', $login);

        // Check whitelisted server
        if (!empty($configuration->xmppwhitelist)
        && !in_array($host, $configuration->xmppwhitelist)) {
            $this->showErrorBlock('unauthorized');
            return;
        }

        // We check if we already have an open session
        $here = App\Session::where('hash', sha1($username.$password.$host))->first();

        $user = User::firstOrNew(['id' => $login]);
        $user->init();
        $user->save();

        $rkey = Key::createNewRandomKey();

        if (!$deviceId) {
            $deviceId = generateKey(16);

            $key = new App\EncryptedPassword;
            $key->user_id = $login;
            $key->id = $deviceId;
            $key->data = Crypto::encrypt($password, $rkey);
            $key->save();

            $this->rpc('Login.setQuick', $deviceId, $login, $host, $rkey->saveToAsciiSafeString());
        }

        if ($here) {
            $this->rpc('Login.setCookie', 'MOVIM_SESSION_ID', $here->id, date(DATE_COOKIE, Cookie::getTime()));
            $this->rpc('MovimUtils.redirect', $this->route('main'));
            return;
        } elseif (App\Session::where('username', $username)->where('host', $host)->exists()) {
            $this->showErrorBlock('wrong_password');
            return;
        }

        $s = new App\Session;
        $s->init($username, $password, $host);
        $s->loadMemory();
        $s->save();

        // Force reload the User to link the new session
        \App\User::me(true);

        // We launch the XMPP socket
        $this->rpc('register', $host);

        \Moxl\Stanza\Stream::init($host);
    }
}
