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
                    He say I know you, you know me<br />
                    One thing I can tell you is<br />
                    You got to be free<br /><br />

                    Come together, right now<br />
                    Over me<br />
                    </blockquote>
                <cite>
                    <a href="http://wikipedia.org/wiki/Lennon/McCartney">John Lennon & Paul McCartney</a>, in
                    <a href="http://wikipedia.org/wiki/Come_Together">"Come Together"</a> on
                    <a href="http://wikipedia.org/wiki/Abbey_Road_(album)">Abbey Road</a> by
                    <a href="http://wikipedia.org/wiki/The_Beatles">The Beatles</a>
                </cite>

		        </div>
			    <form id="connectform" action="index.php" method="post">
		            <?php echo $warning; ?>
			        <input type="email" name="login" id="login" autofocus required
			            placeholder="<?php echo t("My address"); ?>"/><br />
			        <input type="password" name="pass" id="pass" required
			            placeholder="<?php echo t("Password"); ?>"/><br />

			        <?php /*<a href="#" class="showoptions" onclick="getElementById('options').style.display = 'block';"><?php echo t('Options'); ?></a>

                    <fieldset id="options" style="display: none;">
			            <label class="tiny"><?php echo t('First Login'); ?></label>
			                <input type="checkbox" class="tiny" name="create" id="create"><br />
			                <hr />

			            <label class="tiny"><?php echo t('Bosh Host'); ?></label>
			                <input type="text" class="tiny" name="host" id="host"
			                    value="<?php echo $serverconf['defBoshHost']; ?>"  onfocus="myFocus(this);" onblur="myBlur(this);"/><br />

			            <label class="tiny"><?php echo t('Bosh Suffix'); ?></label>
			                <input type="text" class="tiny" name="suffix" id="suffix"
			                    value="<?php echo $serverconf['defBoshSuffix']; ?>"  onfocus="myFocus(this);" onblur="myBlur(this);"/><br />

			            <label class="tiny"><?php echo t('Bosh Port'); ?></label>
			                <input type="text" class="tiny" name="port" id="port"
			                    value="<?php echo $serverconf['defBoshPort']; ?>"  onfocus="myFocus(this);" onblur="myBlur(this);"/><br />
			        </fieldset>*/ ?>
			        <input style="float: right;" onclick="if(document.querySelector('#login').value != '' && document.querySelector('#pass').value != '') {this.value = '<?php echo t('Connecting...');?>'; this.className='button icon loading'}"  type="submit" name="submit" value="<?php echo t("Come in!"); ?>"/>
			        
			        <div style="padding-top: 20px; width: 100%; text-align: center; clear: both;">
			        
			        <?php 
		                global $sdb;
                        $contacts = $sdb->select('ConfVar', array());
                        echo t('This server host %s accounts', count($contacts));
			        ?>
			        </div>
			    </form>
			</div>
	    <?php
	}
}
