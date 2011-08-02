<?php
/**
 * @file ControllerMain.php
 * This file is part of MOVIM.
 *
 * @brief Handles incoming static pages requests.
 *
 * @author Etenil <etenil@etenilsrealm.nl>
 *
 * @version 1.0
 * @date 21 October 2010
 *
 * Copyright (C)2010 MOVIM Project
 *
 * See COPYING for licensing deatils.
 */

class ControllerMain extends ControllerBase
{
	protected $default_handler = 'mainPage';
	private $page;

	function __construct()
	{
		parent::__construct();

		$this->page = new TplPageBuilder();
        $this->page->addScript('hash.js');
        $this->page->addScript('movimrpc.js');
		$this->page->addScript('movim.js');
	}

	function mainPage()
	{
		$user = new User();

		if(!$user->isLogged()) {
			$this->login();
		} else {
			$this->page->setTitle(t('%s - Welcome to Movim', APP_TITLE));
			//$this->page->menuAddLink($this->page->theme_img('img/home_icon.png', 'home_icon').t('Home'), '?q=mainPage', true);
            $this->page->menuAddLink(t('Home'), '?q=mainPage', true);
			$this->page->menuAddLink(t('Configuration'), '?q=config');
			//$this->page->menuAddLink(t('Logout'), '?q=disconnect');
			$content = new TplPageBuilder($user);

			$this->page->setContent($content->build('main.tpl'));
			echo $this->page->build('page.tpl');
		}
	}

	function friend()
	{
		$user = new User();
		if(!$user->isLogged()) {
			$this->login();
		} else {
			if(isset($_GET['f']) && $_GET['f'] != "" ) {
				$this->page->setTitle(t('%s - Welcome to Movim', APP_TITLE));
			    //$this->page->menuAddLink($this->page->theme_img('img/home_icon.png', 'home_icon').t('Home'), '?q=mainPage', true);
                $this->page->menuAddLink(t('Home'), '?q=mainPage');

				$cachevcard = Cache::c('vcard'.$_GET['f']);
				if(isset($cachevcard['vCardFN']) || isset($cachevcard['vCardFamily']))
					$this->page->menuAddLink($cachevcard['vCardFN'] ." ".$cachevcard['vCardFamily'], false, true);
				elseif(isset($cachevcard['vCardNickname']))
					$this->page->menuAddLink($cachevcard['vCardNickname'], false, true);
				else
					$this->page->menuAddLink($_GET['f'], false, true);

				$this->page->menuAddLink(t('Configuration'), '?q=config');
				$content = new TplPageBuilder($user);

				$this->page->setContent($content->build('friend.tpl'));
				echo $this->page->build('page.tpl');
			}
			else
				$this->mainPage();
		}
	}

	function config()
	{
		$user = new User();

		if(!$user->isLogged()) {
			$this->login();
		} else {
			$this->page->setTitle(t('%s - Configuration', APP_TITLE));
			//$this->page->menuAddLink($this->page->theme_img('img/home_icon.png', 'home_icon').t('Home'), '?q=mainPage', true);
            $this->page->menuAddLink(t('Home'), '?q=mainPage');
			$this->page->menuAddLink(t('Configuration'), '?q=config', true);
			//$this->page->menuAddLink(t('Logout'), '?q=disconnect');

			$content = new TplPageBuilder($user);

			$this->page->setContent($content->build('config.tpl'));
			echo $this->page->build('page.tpl');
		}
	}

	function account()
	{
		if(Conf::getServerConfElement("accountCreation") == 1) {
			$this->page->setTitle(t('%s - Account Creation', APP_TITLE));
			//$this->page->menuAddLink($this->page->theme_img('img/home_icon.png', 'home_icon').t('Home'), '?q=mainPage', true);
            $this->page->menuAddLink(t('Home'), '?q=mainPage', true);
			$content = new TplPageBuilder($user);

			$this->page->setContent($content->build('account.tpl'));
			echo $this->page->build('page.tpl');

		} else {
			$this->login();
		}
	}

	/**
	 * Show login interface (hard-coded).
	 */
	function login()
	{
		$this->page->setTitle(t('%s - Login to Movim', APP_TITLE));
		$this->page->menuAddLink('Movim | Human Network', 'http://www.movim.eu/');
		if(Conf::getServerConfElement("accountCreation") == 1)
			$this->page->menuAddLink(t('Account Creation'), '?q=account');
		
		switch ($_GET['err']) {
            case 'noaccount':
			    $warning = '
			            <div class="warning">
			                Wrong username
			            </div> ';
                break;
            case 'invalidjid':
			    $warning = '
			            <div class="warning">
			                Invalid JID
			            </div> ';
                break;
            case 'failauth':
			    $warning = '
			            <div class="warning">
			                The XMPP authentification failed
			            </div> ';
                break;
            case 'bosherror':
			    $warning = '
			            <div class="warning">
			                The current BOSH URL in invalid
			            </div> ';
                break;
        }
        
        if(!BROWSER_COMP)
            $browser_comp = '
			            <div class="warning">
			                '.t('Your web browser is too old to use with Movim.').'
			            </div> ';
		
		$serverconf = Conf::getServerConf();
		
		ob_start();
		?>
		<noscript>
            <style type="text/css">
                #loginpage {display:none;}
            </style>
            <div class="warning">
            <?php echo t("You don't have javascript enabled.  Good luck with that."); ?>
            </div>
        </noscript>
		    <div id="loginpage">
		        <?php echo $browser_comp; ?>
		        
		        <div id="quote">
		            <blockquote>
		                "I'm free! — I'm free,<br />
                        And freedom tastes of reality,<br />
                        I'm free — I'm free,<br />
                        An' I'm waiting for you to follow me.<br />
                    </blockquote>
                <cite>
                    <a href="http://wikipedia.org/wiki/Pete_Townshend">Pete Townshend</a>, in 
                    <a href="http://wikipedia.org/wiki/I'm_Free_(The_Who_song)">"I'm Free"</a> on 
                    <a href="http://wikipedia.org/wiki/Tommy_(album)">Tommy</a> by 
                    <a href="http://wikipedia.org/wiki/The_Who">The Who</a>
                </cite>
                
		        </div>
			    <form id="connectform" action="index.php" method="post">
		            <?php echo $warning; ?> 
			        <input type="email" name="login" id="login" autofocus
			            value="<?php echo t("My address"); ?>" onfocus="myFocus(this);" onblur="myBlur(this);"/><br />
			        <input type="password" name="pass" id="pass" 
			            value="<?php echo t("Password"); ?> "  onfocus="myFocus(this);" onblur="myBlur(this);"/><br />
			            
			        <a href="#" class="showoptions" onclick="getElementById('options').style.display = 'block';"><?php echo t('Options'); ?></a>
			        
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
			        </fieldset>
			        <input class="submit" type="submit" name="submit" value="<?php echo t("Come in!"); ?>"/>
			    </form>
			</div>
	    <?php 
		$this->page->setContent(ob_get_contents());
        ob_end_clean();
		echo $this->page->build('page.tpl');
	}

	function disconnect()
	{
		$user = new User();
		$user->desauth();
		$this->login();
	}
}
