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

class Login extends WidgetBase
{
    function load()
    {
        $this->addcss('login.css');
        $this->addjs('login.js');
        $this->registerEvent('moxlerror', 'onMoxlError');
        $this->registerEvent('session_start_handle', 'onStart');
        $this->registerEvent('storage_get_handle', 'onConfig');
        $this->registerEvent('saslfailure', 'onSASLFailure');
    }

    function onStart($packet)
    {
        $pd = new \Modl\PresenceDAO();
        $pd->clearPresence($this->user->getLogin());

        // http://xmpp.org/extensions/xep-0280.html
        \Moxl\Stanza\Carbons::enable();

        $s = new Get;
        $s->setXmlns('movim:prefs')
          ->request();
    }

    function onConfig($packet)
    {
        RPC::call('postLogin', $this->user->getLogin(), Route::urlize('root'));
    }

    function display()
    {
        $submit = $this->genCallAjax('ajaxLogin', "movim_parse_form('login')");
        
        $cd = new \Modl\ConfigDAO();
        $config = $cd->get();
        
        $this->view->assign('submit', $submit);
        $this->view->assign('info',   $config->info);

        $sd = new \Modl\SessionxDAO();
        $sd->clean();
            
        if(isset($_GET['err'])) {
            $this->view->assign('warnings', $this->displayWarning($_GET['err'], true));
        } else {
            $this->view->assign('warnings', '');
        }
        
        $pop = 0;
        
        foreach(scandir(USERS_PATH) as $f)
            if(is_dir(USERS_PATH.'/'.$f))
                $pop++;

        $this->view->assign('pop', $pop-2);

        $sd = new \Modl\SessionxDAO();
        $connected = $sd->getConnected();

        $this->view->assign('connected', $connected);
        
        $this->view->assign('gmail',
            $this->__('account.gmail',
                '<a href="#" onclick="fillExample(\'your.id@gmail.com \', \'\');">', '</a>'));
                
        $this->view->assign('facebook',
            $this->__('account.facebook',
                '<a href="#" onclick="fillExample(\'your.id@chat.facebook.com \', \'\');">', '</a>'));

        $cd = new \Modl\ConfigDAO();
        $config = $cd->get();
        $whitelist = $config->xmppwhitelist;
        
        if(isset($whitelist) && $whitelist!=''){
            $this->view->assign('whitelist', $whitelist);
            $this->view->assign('whitelist_display', true);
        } else{
            $this->view->assign('whitelist_display', false);
        }

        $user = new User();
        $color = $user->getConfig('color');
        $this->view->assign('color', $color);
    }

    function onMoxlError($error) {
        RPC::call('movim_redirect', Route::urlize('disconnect', $error[1]));
    }

    function onSASLFailure($packet)
    {
        $title = $this->__('error.fail_auth');

        switch($packet->content) {
            case 'not-authorized':
                $warning = $this->__('error.wrong_account');
                break;
            case 'invalid-mechanism':
                $warning = $this->__('error.mechanism');
                break;
            case 'malformed-request':
                $warning = $this->__('error.mechanism');
                break;
            case 'bad-protocol':
                $warning = $this->__('error.fail_auth');
                break;
            case 'bad-auth':
                $warning = $this->__('error.wrong_account');
                break;
            default :
                $warning = $this->__('error.fail_auth');
                break;
        }

        RPC::call('remoteUnregister');
        RPC::call('movim_desktop_notification', $title, $warning);
    }

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
    }

    function ajaxLogin($element)
    {
        // We get the Server Configuration
        $cd = new \Modl\ConfigDAO();
        $config = $cd->get();
        
        $warning = false;

        // Empty input test
        foreach($element as $value) {
            if($value == NULL || $value == '') {
                $warning = 'datamissing';
            }
        }

        $this->displayWarning($warning);

        // Correct email test
        if(!filter_var($element['login'], FILTER_VALIDATE_EMAIL))
            $warning = 'invalidjid';

        $this->displayWarning($warning);
        
        // Check whitelisted server
        if(
            $config->xmppwhitelist != '' &&!
            in_array(
                end(
                    explode('@', $element['login'])
                    ), 
                explode(',',$config->xmppwhitelist)
                )
            )
            $warning = 'serverunauthorized';
        $this->displayWarning($warning);

        // Correct XMPP account test
        $login_arr = explode('@', $element['login']);
        $user = $login_arr[0];
        $host = $login_arr[1];
        
        $dns = dns_get_record('_xmpp-client._tcp.'.$login_arr[1]);

        if(isset($dns[0]['target']) && $dns[0]['target'] != null)
            $domain = $dns[0]['target'];
        else {
            $domain = $host;
        }

        $this->displayWarning($warning);

        // We create a new session or clear the old one
        $s = Sessionx::start();
        
        $s->init($user, $element['pass'], $host, $domain);

        // We save the loaded widgets list in the database
        $wrapper = WidgetWrapper::getInstance(false);

        $sess = Session::start(APP_NAME);
        $sess->set('registered_events', $wrapper->registerEvents());

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
        RPC::commit();
    }
}
