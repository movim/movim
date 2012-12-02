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

class Login extends WidgetBase {

    function WidgetLoad()
    {
        $this->addcss('login.css');
        $this->addjs('login.js');
        $this->registerEvent('config', 'onConfig');
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
                                '.t('Authentification mechanism not supported by Movim').'
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
                    break;
            }

            if($htmlonly)
                return $warning;
            else {
                RPC::call('movim_fill', 'warning',
                   RPC::cdata($warning));
                RPC::call('loginButtonSet', t("Come in!"));

                RPC::commit();
                exit;
            }
        }
    }

    function ajaxLogin($element)
    {
        // We get the Server Configuration
        $serverconfig = Conf::getServerConf();
        
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
            $warning = 'dnsdomain';
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
                    'ressource' => 'moxl'.md5(date('c')),

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

        // BOSH + XMPP connexion test
        $warning = moxl\login();
        if($warning != 'OK')
            $this->displayWarning($warning);

        RPC::call('enterMovim', BASE_URI.'?q=main');
        RPC::commit();
    }

    function ajaxGetConfig()
    {
        $s = new moxl\StorageGet();
        $s->setXmlns('movim:prefs')
          ->request();        $evt = new \Event();
        $evt->runEvent('nostream');
    }

	function build()
	{
        $submit = $this->genCallAjax('ajaxLogin', "movim_parse_form('login')");

        ?>
        <div id="loginpage">
        <?php
            if(!BROWSER_COMP) {
            echo '
                <div class="message warning">
                    '.t('Your web browser is too old to use with Movim.').'
                </div> ';
            } else {
        ?>
                
                <form
                    name="login"
                    id="connectform"
                    onkeypress="
                        if(event.keyCode == 13) {
                            <?php echo $submit; ?> loginButtonSet('<?php echo t('Connecting...');?>', true); this.onclick=null;}">
                    <div id="warning"><?php echo $this->displayWarning($_GET['err'], true); ?></div>
                    <div class="element">
                        <input type="email" name="login" id="login" autofocus required
                            placeholder="<?php echo t("My address"); ?>"/>
                    </div>
                    <div class="element">
                        <input type="password" name="pass" id="pass" required
                            placeholder="<?php echo t("Password"); ?>"/>
                    </div>
                    <div class="element">
                        <a
                            class="button icon yes"
                            onclick="<?php echo $submit; ?> loginButtonSet('<?php echo t('Connecting...');?>', true); this.onclick=null;"
                            id="submit"
                            name="submit"><?php echo t("Come in!"); ?></a>
                    </div>
                    <div class="infos">
                            <?php
                            $query = CacheVar::query()->where();
                            $contacts = CacheVar::run_query($query);
                            echo t('Population').' '.ceil(count($contacts)/2).' • ';
                            ?>
                            <?php echo t('No account yet ?'); ?>
                            <a href="?q=account">
                                <?php echo t('Create one !'); ?>
                            </a>
                    </div>
                                <div class="clear"></div>
                </form>
                
            <?php
            }
            ?>


        </div>
    <?php

	}
}
