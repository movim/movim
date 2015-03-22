<?php
/**
 * @package Widgets
 *
 * @file Login.php
 * This file is part of MOVIM.
 *
 * @brief The login form.
 *
 * @author Timothée Jaussoin <edhelas@gmail.com>
 *
 * @version 1.0
 * @date 07 December 2011
 *
 * Copyright (C)2010 MOVIM project
 *
 * See COPYING for licensing information.
 */

use Moxl\Xec\Action\Storage\Get;
use Moxl\Xec\Action\Roster\GetList;
use Respect\Validation\Validator;

class Login extends WidgetBase
{
    function load()
    {
        $this->addcss('login.css');
        $this->addjs('login.js');
        $this->registerEvent('moxlerror', 'onMoxlError');
        $this->registerEvent('session_start_handle', 'onStart');
        $this->registerEvent('saslfailure', 'onSASLFailure');
        $this->registerEvent('storage_get_handle', 'onConfig');
        $this->registerEvent('storage_get_errorfeaturenotimplemented', 'onConfig');
    }

    function onStart($packet)
    {
        $pd = new \Modl\PresenceDAO();
        $pd->clearPresence($this->user->getLogin());

        // http://xmpp.org/extensions/xep-0280.html
        \Moxl\Stanza\Carbons::enable();

        // We refresh the roster
        $r = new GetList;
        $r->request();

        // We get the configuration
        $s = new Get;
        $s->setXmlns('movim:prefs')
          ->request();
    }

    function onConfig($packet)
    {
        $this->user->createDir();
        RPC::call('Login.post', $this->user->getLogin(), Route::urlize('root'));
    }

    function display()
    {
        $submit = $this->call('ajaxLogin', "movim_form_to_json('login')");
        
        $cd = new \Modl\ConfigDAO();
        $config = $cd->get();
        
        $this->view->assign('submit',   $submit);
        $this->view->assign('info',     $config->info);
        $this->view->assign('whitelist',$config->xmppwhitelist);
        
        $pop = 0;
        
        foreach(scandir(USERS_PATH) as $f)
            if(is_dir(USERS_PATH.'/'.$f))
                $pop++;

        $this->view->assign('pop', $pop-2);

        $sd = new \Modl\SessionxDAO();
        $connected = $sd->getConnected();

        $this->view->assign('connected', $connected);

        $this->view->assign('error', $this->prepareError());
    }

    function showErrorBlock($error)
    {
        RPC::call('movim_fill', 'error', $this->prepareError($error));
        RPC::call('movim_add_class', '#login_widget', 'error');
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
    
    function onMoxlError($error) {
        RPC::call('movim_redirect', Route::urlize('disconnect', $error[1]));
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
    /*
    private function displayWarning($warning, $htmlonly = false)
    {
        if($warning != false) {
            switch ($warning) {
                case 'noaccount':
                    $warning = '
                            <div class="message warning">
                                '.$this->__('error.username').'
                            </div> ';
                    break;
                case 'invalidjid':
                    $warning = '
                            <div class="message warning">
                                '.$this->__('error.jid').'
                            </div> ';
                    break;
                case 'errormechanism':
                    $warning = '
                            <div class="message error">
                                '.$this->__('error.mechanism').'
                            </div> ';
                    break;
                case 'errorchallenge':
                    $warning = '
                            <div class="message error">
                                '.$this->__('error.empty_challenge').'
                            </div> ';
                    break;
                case 'dnsdomain':
                    $warning = '
                            <div class="message error">
                                '.$this->__('error.dns').'
                            </div> ';
                    break;
                case 'datamissing':
                    $warning = '
                            <div class="message error">
                                '.$this->__('error.data_missings').'
                            </div> ';
                    break;
                case 'wrongpass':
                    $warning = '
                            <div class="message warning">
                                '.$this->__('error.wrong_password').'
                            </div> ';
                    break;
                case 'failauth':
                    $warning = '
                            <div class="message warning">
                                '.$this->__('error.fail_auth').'
                            </div> ';
                    break;
                case 'bosherror':
                    $warning = '
                            <div class="message error">
                                '.$this->__('error.bosh_invalid').'
                            </div> ';
                    break;
                case 'internal':
                    $warning = '
                            <div class="message error">
                                '.$this->__('error.internal').'
                            </div> ';
                    break;
                case 'session':
                    $warning = '
                            <div class="message error">
                                '.$this->__('error.session').'
                            </div> ';
                    break;
                case 'acccreated':
                    $warning = '
                            <div class="message success">
                                '.$this->__('error.account_created').'
                            </div> ';
                    break;
                case 'wrongaccount':
                    $warning = '
                            <div class="message error">
                                '.$this->__('error.wrong_account').'
                            </div> ';
                    break;
                case 'serverunauthorized':
                    $warning = '
                            <div class="message warning">
                                '.$this->__('error.xmpp_unauthorized').'
                            </div>';
                case 'mecerror':
                    $warning = '
                            <div class="message warning">
                                '.$this->__('error.mec_error').'
                            </div>';
                    break;
                default: 
                    $warning = '
                            <div class="message error">
                                '.$warning.'
                            </div>';
                    break;
            }

            if($htmlonly)
                return $warning;
            else {
                RPC::call('movim_fill', 'warning', $warning);
                RPC::call('loginButtonSet', $this->__("button.come_in"));

                RPC::commit();

                exit;
            }
        }
    }*/

    function ajaxCheckLogin($jid)
    {
        list($username, $host) = explode('@', $jid);
        $sd = new \Modl\SessionxDAO;
        $here = $sd->checkConnected($username, $host);

        if($here) {
            $message = $this->__('error.impossible') . ' : '.$this->__('error.conflict');
            Notification::append(null, $message);
        }
    }

    function ajaxLogin($form)
    {
        // We get the Server Configuration
        $cd = new \Modl\ConfigDAO;
        $config = $cd->get();

        // First we check the form
        $validate_login   = Validator::email()->length(6, 40);
        $validate_password = Validator::string()->length(4, 40);

        $login    = $form->login->value;
        $password = $form->pass->value;

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
        $here = $sd->checkConnected($username, $host);

        if($here) {
            $this->showErrorBlock('conflict');
            return;
        }

        // We try to get the domain
        $dns = dns_get_record('_xmpp-client._tcp.'.$login_arr[1]);

        if(isset($dns[0]['target']) && $dns[0]['target'] != null)
            $domain = $dns[0]['target'];
        else {
            $domain = $host;
        }

        // We create a new session or clear the old one
        $s = Sessionx::start();
        $s->init($username, $password, $host, $domain);

        \Moxl\Stanza\Stream::init($host);
    }

    function ajaxGetRememberedSession($sessions)
    {
        $sessions = json_decode($sessions);

        $sessions_grabbed = array();

        $cd = new \Modl\ContactDAO;
        
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

        $sessionshtml = $this->tpl();
        $sessionshtml->assign('sessions', $sessions_grabbed);
        $sessionshtml->assign('empty', new \Modl\Contact);

        RPC::call('movim_fill', 'sessions', $sessionshtml->draw('_login_sessions', true));
        RPC::call('Login.refresh');
    }
}
