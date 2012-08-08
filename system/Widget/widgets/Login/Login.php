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
    }
    
    private function displayWarning($warning) 
    {
        if($warning != false) {
            switch ($warning) {
                case 'noaccount':
                    $warning = '
                            <div class="warning">
                                '.t('Wrong username').'
                            </div> ';
                    break;
                case 'invalidjid':
                    $warning = '
                            <div class="warning">
                                '.t('Invalid JID').'
                            </div> ';
                    break;
                case 'errormechanism':
                    $warning = '
                            <div class="error">
                                '.t('Authentification mechanism not supported by Movim').'
                            </div> '; 
                    break;
                case 'errorchallenge':
                    $warning = '
                            <div class="error">
                                '.t('Empty Challenge from the server').'
                            </div> '; 
                    break;
                case 'dnsdomain':
                    $warning = '
                            <div class="error">
                                '.t('XMPP Domain error, your account is not a correct Jabber ID').'
                            </div> ';
                    break;
                case 'datamissing':
                    $warning = '
                            <div class="error">
                                '.t('Some data are missing !').'
                            </div> ';
                    break;
                case 'wrongpass':
                    $warning = '
                            <div class="warning">
                                '.t('Wrong password').'
                            </div> ';
                    break;
                case 'failauth':
                    $warning = '
                            <div class="warning">
                                '.t('The XMPP authentification failed').'
                            </div> ';
                    break;
                case 'bosherror':
                    $warning = '
                            <div class="warning">
                                '.t('The current BOSH URL in invalid').'
                            </div> ';
                    break;
                case 'internal':
                    $warning = '
                            <div class="error">
                                '.t('Internal server error').'
                            </div> ';
                    break;
                case 'session':
                    $warning = '
                            <div class="error">
                                '.t('Session error').'
                            </div> ';
                    break;
                case 'acccreated':
                    $warning = '
                            <div class="error valid">
                                '.t('Account successfully created').'
                            </div> ';
                    break;
                case 'wrongaccount':
                    $warning = '
                            <div class="error">
                                '.t('Movim failed to authenticate. You entered wrong data').'
                            </div> ';
                    break;
            }
            
            RPC::call('movim_fill', 'warning',
               RPC::cdata($warning));
               
            RPC::commit();
            exit;
        }
    }
    
    function ajaxLogin($element)
    {
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
                    'url' => 'localhost:5280/http-bind',
                    'port'=> 5222,
                    'host'=> $host,
                    'domain' => $domain,
                    'ressource' => 'moxl', 
                    
                    'user'     => $user,
                    'password' => $element['pass']);
        }
        
        $sess = Session::start(APP_NAME);

        $sess->set('session', $session);

        // BOSH + XMPP connexion test
        $warning = moxl\login();
        if($warning != 'OK')
            $this->displayWarning($warning);
        
        RPC::call('enterMovim', BASE_URI.'?q=mainPage');
        RPC::commit();
    }
	
	function build()
	{ 
        $submit = $this->genCallAjax('ajaxLogin', "movim_parse_form('login')");
        ?>
        <div id="loginpage">
            <?php
            if(file_exists(BASE_PATH.'install/part1.php')) { ?>
                <div class="warning">
                <?php echo t('Please remove the %s folder in order to complete the installation', 'install/'); ?>
                </div>
            <?php
            }?>
            <div id="warning"></div>
            <form name="login" id="connectform">
                <input type="email" name="login" id="login" />
                <input type="password" name="pass" id="pass" />

                <input 
                    onclick="<?php echo $submit; ?>"  
                    type="button"
                    name="submit" value="Come In!"/>
            </form>
        </div>
    <?php
/*        switch ($_GET['err']) {
            case 'noaccount':
	            $warning = '
	                    <div class="warning">
	                        '.t('Wrong username').'
	                    </div> ';
                break;
            case 'invalidjid':
	            $warning = '
	                    <div class="warning">
	                        '.t('Invalid JID').'
	                    </div> ';
                break;
            case 'wrongpass':
	            $warning = '
	                    <div class="warning">
	                        '.t('Wrong password').'
	                    </div> ';
                break;
            case 'failauth':
	            $warning = '
	                    <div class="warning">
	                        '.t('The XMPP authentification failed').'
	                    </div> ';
                break;
            case 'bosherror':
	            $warning = '
	                    <div class="warning">
	                        '.t('The current BOSH URL in invalid').'
	                    </div> ';
                break;
            case 'internal':
	            $warning = '
	                    <div class="error">
	                        '.t('Internal server error').'
	                    </div> ';
                break;
            case 'session':
	            $warning = '
	                    <div class="error">
	                        '.t('Session error').'
	                    </div> ';
                break;
            case 'acccreated':
	            $warning = '
	                    <div class="error valid">
	                        '.t('Account successfully created').'
	                    </div> ';
                break;
            case 'wrongaccount':
	            $warning = '
	                    <div class="error">
	                        '.t('Movim failed to authenticate. You entered wrong data').'
	                    </div> ';
                break;
        }

        if(!BROWSER_COMP)
            $browser_comp = '
			            <div class="warning">
			                '.t('Your web browser is too old to use with Movim.').'
			            </div> ';

		$serverconf = Conf::getServerConf();
        echo $browser_comp; 
		        ?>

		    <div id="loginpage">
			    <form id="connectform" action="index.php" method="post">
		            <?php echo $warning; ?>
                    <div id="cells">
                        <input type="email" name="login" id="login" autofocus required
                            placeholder="<?php echo t("My address"); ?>"/>
                        <input type="password" name="pass" id="pass" required
                            placeholder="<?php echo t("Password"); ?>"/>

                        <input 
                            onclick="if(document.querySelector('#login').value != '' && document.querySelector('#pass').value != '') {this.value = '<?php echo t('Connecting...');?>'; this.className='button icon loading'}"  
                            type="submit" 
                            name="submit" value="<?php echo t("Come in!"); ?>"/>
			        </div>
	
                    <span>
			        <?php 
                        //$query = ConfVar::query();
                        //$contacts = ConfVar::run_query($query);

                        $contacts = ConfVar::run_query(ConfVar::query()->select());
                        $conf = Conf::getServerConf();
                        
                        echo t('This server hosts %s accounts', count($contacts));
			        
                        if($conf["accountCreation"] == 1
                           && ($conf['maxUsers'] == -1
                               || count($contacts) < $conf['maxUsers'])) {
                    ?> - 
                            <a href="?q=accountCreate"><?php echo t('Create a new account'); ?></a> - 
                            <a href="?q=accountAdd"><?php echo t('Link my current account'); ?></a>
                    <?php
                        }
                    ?>
                    </span>
			        </div>
			    </form>
			</div>*/
	}
}
