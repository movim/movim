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
    }
    
	
	function build()
	{ 
        switch ($_GET['err']) {
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
	                        '.t('Movim fail to authenticate. You entered wrong data').'
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
                        $query = ConfVar::query();
                        $contacts = ConfVar::run_query($query);
                        echo t('This server host %s accounts', count($contacts));
			        
                        if(Conf::getServerConfElement("accountCreation") == 1) {
                    ?> - 
                            <a href="?q=accountCreate"><?php echo t('Create a new account'); ?></a> - 
                            <a href="?q=accountAdd"><?php echo t('Link my current account'); ?></a>
                    <?php
                        }
                    ?>
                    </span>
			        </div>
			    </form>
			</div>
	    <?php
        if(file_exists(BASE_PATH.'install/part1.php')) { ?>
            <div class="warning" style="margin-top: 40px;">
            <?php echo t('Please remove the %s folder in order to complete the installation', 'install/'); ?>
            </div>
        <?php
        }
	}
}
