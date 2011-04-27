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
			$this->page->menuAddLink($this->page->theme_img('img/home_icon.png', 'home_icon').t('Home'), '?q=mainPage', true);
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
				$this->page->menuAddLink($this->page->theme_img('img/home_icon.png', 'home_icon').t('Home'), '?q=mainPage');

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
			$this->page->menuAddLink($this->page->theme_img('img/home_icon.png', 'home_icon').t('Home'), '?q=mainPage');
			$this->page->menuAddLink(t('Configuration'), '?q=config', true);
			//$this->page->menuAddLink(t('Logout'), '?q=disconnect');

			$content = new TplPageBuilder($user);

			$this->page->setContent($content->build('config.tpl'));
			echo $this->page->build('page.tpl');
		}
	}

	function account()
	{
		if(GetConf::getServerConfElement("accountCreation") == 1) {
			$this->page->setTitle(t('%s - Account Creation', APP_TITLE));
			$this->page->menuAddLink($this->page->theme_img('img/home_icon.png', 'home_icon').t('Home'), '?q=mainPage');
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
		$this->page->menuAddLink($this->page->theme_img('img/home_icon.png', 'home_icon').'Movim | Human Network', 'http://www.movim.eu/', true);
		if(Conf::getServerConfElement("accountCreation") == 1)
			$this->page->menuAddLink(t('Account Creation'), '?q=account');
		if($_GET['err'] == 'auth') {
			$this->page->setContent(
				'<div class="warning">'.
				t('Changing these data can be dangerous and may compromise the connection to the Jabber server')
				.'</div>');
		}
		$this->page->setContent(
			'<div id="connect_form">'.
			'<form id="authForm" action="index.php" method="post">'.
			'<input type="text" name="login" id="login" value="'.t("My address").'"  onfocus="myFocus(this);" onblur="myBlur(this);"/>'.
			'<input type="password" name="pass" id="pass" value="'.t("Password").'"  onfocus="myFocus(this);" onblur="myBlur(this);"/><br />'.
			'<input class="submit" style="float: none;" type="submit" name="submit" value="'.t("Come in!").'"/>'.
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
