<?php
if (!defined('DOCUMENT_ROOT')) die('Access denied');
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

class Login extends WidgetBase {

    function WidgetLoad()
    {
        $this->addcss('login.css');
        $this->addjs('login.js');
        $this->registerEvent('config', 'onConfig');
        $this->registerEvent('moxlerror', 'onMoxlError');
        
        $submit = $this->genCallAjax('ajaxLogin', "movim_parse_form('login')");
        $this->view->assign('submit', $submit);
        $this->view->assign('conf',   \system\Conf::getServerConf($submit));
        $this->view->assign('submit_event', 
            'document.getElementById(\'submitb\').click();
            '.$submit.'
            loginButtonSet(\''.t('Connecting...').'\', true); 
            this.onclick=null;');
            
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
        
        $this->view->assign('gmail',
            t('%sGmail accounts are also compatible%s but are not fully supported',
                '<a href="#" onclick="fillExample(\'your.id@gmail.com \', \'\');">', '</a>'));
                
        $this->view->assign('facebook',
            t('You can login with Facebook (chat only) using %syour.id@chat.facebook.com%s and your password',
                '<a href="#" onclick="fillExample(\'your.id@chat.facebook.com \', \'\');">', '</a>'));
        
        $conf = \system\Conf::getServerConf();
        $whitelist = $conf['xmppWhiteList'];
        
        if(isset($whitelist) && $whitelist!=''){
            $this->view->assign('whitelist', $whitelist);
            $this->view->assign('whitelist_display', true);
        } else{
            $this->view->assign('whitelist_display', false);
        }
    }

    function onMoxlError($error) {
        RPC::call('movim_redirect', Route::urlize('disconnect', $error[1]));
    }

    function onConfig(array $data)
    {
        $this->user->setConfig($data);
    }

    private function displayWarning($warning, $htmlonly = false)
    {
        if($warning != false) {
            switch ($warning) {
                case 'noaccount':
                    $warning = '
                            <div class="message warning">
                                '.t('Wrong username').'
                            </div> ';
                    break;
                case 'invalidjid':
                    $warning = '
                            <div class="message warning">
                                '.t('Invalid JID').'
                            </div> ';
                    break;
                case 'errormechanism':
                    $warning = '
                            <div class="message error">
                                '.t('Authentication mechanism not supported by Movim').'
                            </div> ';
                    break;
                case 'errorchallenge':
                    $warning = '
                            <div class="message error">
                                '.t('Empty Challenge from the server').'
                            </div> ';
                    break;
                case 'dnsdomain':
                    $warning = '
                            <div class="message error">
                                '.t('XMPP Domain error, your account is not a correct Jabber ID').'
                            </div> ';
                    break;
                case 'datamissing':
                    $warning = '
                            <div class="message error">
                                '.t('Some data are missing !').'
                            </div> ';
                    break;
                case 'wrongpass':
                    $warning = '
                            <div class="message warning">
                                '.t('Wrong password').'
                            </div> ';
                    break;
                case 'failauth':
                    $warning = '
                            <div class="message warning">
                                '.t('The XMPP authentification failed').'
                            </div> ';
                    break;
                case 'bosherror':
                    $warning = '
                            <div class="message error">
                                '.t('The current BOSH URL in invalid').'
                            </div> ';
                    break;
                case 'internal':
                    $warning = '
                            <div class="message error">
                                '.t('Internal server error').'
                            </div> ';
                    break;
                case 'session':
                    $warning = '
                            <div class="message error">
                                '.t('Session error').'
                            </div> ';
                    break;
                case 'acccreated':
                    $warning = '
                            <div class="message success">
                                '.t('Account successfully created').'
                            </div> ';
                    break;
                case 'wrongaccount':
                    $warning = '
                            <div class="message error">
                                '.t('Movim failed to authenticate. You entered wrong data').'
                            </div> ';
                    break;
                case 'serverunauthorized':
                    $warning = '
                            <div class="message warning">
                                '.t('Your XMPP server is unauthorized').'
                            </div>';
                case 'mecerror':
                    $warning = '
                            <div class="message warning">
                                '.t('The server takes too much time to respond').'
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
                RPC::call('loginButtonSet', t("Come in!"));

                RPC::commit();
            }
        }
    }

    function ajaxLogin($element)
    {
        // We get the Server Configuration
        $serverconfig = \system\Conf::getServerConf();
        
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
            $serverconfig['xmppWhiteList'] != '' &&!
            in_array(
                end(
                    explode('@', $element['login'])
                    ), 
                explode(',',$serverconfig['xmppWhiteList'])
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
            //$warning = 'dnsdomain';
        }

        $this->displayWarning($warning);

        global $session;

        if($s != false) {
            $session = $sess->get('session');
        }
        else {
            $session = array(
                    'rid' => 1,
                    'sid' => 0,
                    'id'  => 0,
                    'url' => $serverconfig['boshUrl'],
                    'port'=> 5222,
                    'host'=> $host,
                    'domain' => $domain,
                    'ressource' => 'moxl'.substr(md5(date('c')), 3, 6),

                    'user'     => $user,
                    'password' => $element['pass'],

                    'proxyenabled' => $serverconfig['proxyEnabled'],
                    'proxyurl' => $serverconfig['proxyURL'],
                    'proxyport' => $serverconfig['proxyPort'],
                    'proxyuser' => $serverconfig['proxyUser'],
                    'proxypass' => $serverconfig['proxyPass']);
        }

        $sess = Session::start(APP_NAME);

        $sess->set('session', $session);
        
        $wrapper = WidgetWrapper::getInstance(false);
        
        $sess->set('registered_events', $wrapper->register_events());

        // BOSH + XMPP connexion test
        $warning = moxl\login();
        
        if($warning != 'OK') {
            //$this->displayWarning($warning);
            RPC::call('movim_redirect', Route::urlize('login', $warning));        
            RPC::commit();
        } else {
            $pd = new modl\PresenceDAO();
            $pd->clearPresence($element['login']);
        
            RPC::call('movim_redirect', Route::urlize('main'));            
            RPC::commit();
        }
    }

    function ajaxGetConfig()
    {
        $s = new moxl\StorageGet();
        $s->setXmlns('movim:prefs')
          ->request();        $evt = new \Event();
        $evt->runEvent('nostream');
    }
}
