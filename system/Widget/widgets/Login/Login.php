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
    
    function __construct() {
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
        }

        if(!BROWSER_COMP)
            $browser_comp = '
			            <div class="warning">
			                '.t('Your web browser is too old to use with Movim.').'
			            </div> ';

		$serverconf = Conf::getServerConf();
		?>
		    <div id="loginpage">
		        <?php echo $browser_comp; 
		        if(file_exists(BASE_PATH.'install/part1.php')) { ?>
                    <div class="warning">
                    <?php echo t('Please remove the %s folder in order to complete the installation', 'install/'); ?>
                    </div>
                <?php
                }
		        ?>

		        <div id="quote">
                    <blockquote>
                        We don't need no education<br />
                        We dont need no thought control<br />
                        No dark sarcasm in the classroom<br />
                        Teachers leave them kids alone<br />
                        Hey! Teachers! Leave them kids alone!<br /><br />
                        All in all it's just another brick in the wall.<br />
                        All in all you're just another brick in the wall.<br />
                    </blockquote>
                <cite>
                    <a href="http://en.wikipedia.org/wiki/Roger_Waters">Roger Waters</a>, in
                    <a href="http://en.wikipedia.org/wiki/Another_Brick_in_the_Wall_%28Part_2%29#Part_2">"Another Brick in the Wall (Part II)"</a> on
                    <a href="http://en.wikipedia.org/wiki/The_Wall">The Wall</a> by
                    <a href="http://en.wikipedia.org/wiki/Pink_Floyd">Pink Floyd</a>
                </cite>

		        </div>
			    <form id="connectform" action="index.php" method="post">
		            <?php echo $warning; ?>
			        <input type="email" name="login" id="login" autofocus required
			            placeholder="<?php echo t("My address"); ?>"/><br />
			        <input type="password" name="pass" id="pass" required
			            placeholder="<?php echo t("Password"); ?>"/><br />

			        <input style="float: right;" onclick="if(document.querySelector('#login').value != '' && document.querySelector('#pass').value != '') {this.value = '<?php echo t('Connecting...');?>'; this.className='button icon loading'}"  type="submit" name="submit" value="<?php echo t("Come in!"); ?>"/>
			        
			        <div style="padding-top: 20px; width: 100%; text-align: center; clear: both;">
			        
			        <?php 
                        $query = ConfVar::query();
                        $contacts = ConfVar::run_query($query);
                        echo t('This server host %s accounts', count($contacts));
			        ?>
			        </div>
			    </form>
			</div>
	    <?php
	}
}
