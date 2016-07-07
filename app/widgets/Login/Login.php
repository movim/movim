<?php

use Moxl\Xec\Action\Storage\Get;
use Moxl\Xec\Action\Roster\GetList;
use Respect\Validation\Validator;

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
    }

    function onStart($packet)
    {
        $session = \Session::start();

        if($session->get('mechanism') != 'ANONYMOUS') {
            // http://xmpp.org/extensions/xep-0280.html
            \Moxl\Stanza\Carbons::enable();

            // We refresh the roster
            $r = new GetList;
            $r->request();

            // We refresh the messages
            //$c = new Chats;
            //$c->ajaxGetHistory();

            // We get the configuration
            $s = new Get;
            $s->setXmlns('movim:prefs')
              ->request();
        }
    }

    function onConfig($packet)
    {
        $this->user->createDir();
        RPC::call('Login.post', $this->user->getLogin(), Route::urlize('root'));
    }

    function display()
    {
        $submit = $this->call('ajaxLogin', "MovimUtils.formToJson('login')");

        $cd = new \Modl\ConfigDAO();
        $config = $cd->get();

        $this->view->assign('submit',   $submit);
        $this->view->assign('info',     $config->info);
        $this->view->assign('whitelist',$config->xmppwhitelist);

        if(isset($config->xmppdomain)
        && !empty($config->xmppdomain)) {
            $this->view->assign('domain', $config->xmppdomain);
        } else {
            $this->view->assign('domain', 'movim.eu');
        }

        $pop = 0;

        foreach(scandir(USERS_PATH) as $f)
            if(is_dir(USERS_PATH.'/'.$f))
                $pop++;

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
        RPC::call('movim_fill', 'error', $this->prepareError($error));
        RPC::call('MovimUtils.addClass', '#login_widget', 'error');
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

    function onSASLFailure($packet)
    {
        switch($packet->content) {
            case 'not-authorized':
                $error = 'wrong_account';
                break;
            case 'invalid-mechanism':
                $error = 'mechanism';
                break;
            case 'malformed-request':
                $error = 'mechanism';
                break;
            case 'bad-protocol':
                $error = 'fail_auth';
                break;
            case 'bad-auth':
                $error = 'wrong_account';
                break;
            default :
                $error = 'fail_auth';
                break;
        }

        $this->showErrorBlock($error);
    }

    function ajaxLogin($form)
    {
        $username = $form->username->value;
        $password = $form->password->value;

        $this->doLogin($username, $password);
    }

    function ajaxHTTPLogin($login, $password)
    {
        $this->doLogin($login, $password);
    }

    private function doLogin($login, $password)
    {
        // We get the Server Configuration
        $cd = new \Modl\ConfigDAO;
        $config = $cd->get();

        // First we check the form
        $validate_login   = Validator::email()->length(1, 254);
        $validate_password = Validator::stringType()->length(1, 128);

        if(!$validate_login->validate($login)) {
            $this->showErrorBlock('login_format');
            return;
        }

        if(!$validate_password->validate($password)) {
            $this->showErrorBlock('password_format');
            return;
        }

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

        if($here) {
        //if($s->get('hash') == sha1($username.$password.$host)) {
            RPC::call('Login.setCookie', $here->session);
            RPC::call('MovimUtils.redirect', Route::urlize('main'));
            $this->showErrorBlock('conflict');
            return;
        }

        $s = Session::start();

        // We create a new session or clear the old one
        $s->set('password', $password);
        $s->set('username', $username);
        $s->set('host', $host);
        $s->set('jid', $login);
        $s->set('hash', sha1($username.$password.$host));

        $s = Sessionx::start();
        $s->init($username, $password, $host);

        // We launch the XMPP socket
        RPC::call('register', $host);

        \Moxl\Stanza\Stream::init($host);
    }

    function ajaxGetRememberedSession($sessions)
    {
        $sessions = json_decode($sessions);

        $sessions_grabbed = [];

        $cd = new \Modl\ContactDAO;

        if(is_array($sessions)) {
            foreach($sessions as $s) {
                $c = $cd->get($s);

                if($c != null) {
                    array_push($sessions_grabbed, $c);
                } else {
                    $c = new \Modl\Contact;
                    $c->jid = $s;
                    array_push($sessions_grabbed, $c);
                }
            }
        }

        $sessionshtml = $this->tpl();
        $sessionshtml->assign('sessions', $sessions_grabbed);
        $sessionshtml->assign('empty', new \Modl\Contact);

        RPC::call('movim_fill', 'sessions', $sessionshtml->draw('_login_sessions', true));
        RPC::call('Login.refresh');
    }
}
