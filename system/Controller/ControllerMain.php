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
		if($_GET['err'] == 'auth') {
			$this->page->setContent(
				'<div class="warning">'.
				t('Changing these data can be dangerous and may compromise the connection to the Jabber server')
				.'</div>');
		}
		
		$serverconf = Conf::getServerConf();
		var_dump($serverconf);
		
		$this->page->setContent(
		    '<div id="loginpage">'.
		        '<div id="quote">
		            <blockquote>'.
		                "I'm free! — I'm free,<br />
                        And freedom tastes of reality,<br />
                        I'm free — I'm free,<br />
                        An' I'm waiting for you to follow me.<br />
                    </blockquote>
                <cite>
                    <a href=\"http://wikipedia.org/wiki/Pete_Townshend\">Pete Townshend</a>, in 
                    <a href=\"http://wikipedia.org/wiki/I'm_Free_(The_Who_song)\">\"I'm Free\"</a> on 
                    <a href=\"http://wikipedia.org/wiki/Tommy_(album)\">Tommy</a> by 
                    <a href=\"http://wikipedia.org/wiki/The_Who\">The Who</a>
                </cite>
                ".
		        '</div>'.
			    '<form id="connectform" action="index.php" method="post">'.
			        '<input type="text" name="login" id="login" 
			            value="'.t("My address").'" onfocus="myFocus(this);" onblur="myBlur(this);"/><br />'.
			        '<input type="password" name="pass" id="pass" 
			            value="'.t("Password").'"  onfocus="myFocus(this);" onblur="myBlur(this);"/><br />'.
			            
			        '<a href="#" class="showoptions" onclick="getElementById(\'options\').style.display = \'block\';">'.t('Options').'</a>'.
			        
                    '<fieldset id="options" style="display: none;">'.
			            '<label class="tiny">'.t('Bosh Host').'</label>
			                <input type="text" class="tiny" name="host" id="host" 
			                    value="'.$serverconf['defBoshHost'].'"  onfocus="myFocus(this);" onblur="myBlur(this);"/><br />'.
			                    
			            '<label class="tiny">'.t('Bosh Suffix').'</label>
			                <input type="text" class="tiny" name="suffix" id="suffix" 
			                    value="'.$serverconf['defBoshSuffix'].'"  onfocus="myFocus(this);" onblur="myBlur(this);"/><br />'.
			                    
			            '<label class="tiny">'.t('Bosh Port').'</label>
			                <input type="text" class="tiny" name="port" id="port" 
			                    value="'.$serverconf['defBoshPort'].'"  onfocus="myFocus(this);" onblur="myBlur(this);"/><br />'.
			        '</fieldset>'.
			        '<input class="submit" type="submit" name="submit" value="'.t("Come in!").'"/>'.
			    '</form>'.
			'</div>');
		echo $this->page->build('page.tpl');
	}

	function disconnect()
	{
		$user = new User();
		$user->desauth();
		$this->login();
	}
}
