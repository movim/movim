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
	protected $default_handler = 'main';
	private $page;

	function __construct()
	{
		parent::__construct();

		$this->page = new TplPageBuilder();
		$this->page->addScript('movim.js');
        $this->page->addScript('hash.js');
        $this->page->addScript('movimrpc.js');
	}

	function main()
	{
		$user = new User();

		if(!$user->isLogged()) {
			$this->login();
		} else {
			$this->page->setTitle(t('%s - Welcome to Movim', APP_TITLE));
            $this->page->menuAddLink(t('Home'), 'main', true);
            $this->page->menuAddLink(t('Explore'), 'explore');
            $this->page->menuAddLink(t('Profile'), 'profile');
            $this->page->menuAddLink(t('Media'), 'media');
			$this->page->menuAddLink(t('Configuration'), 'config');
            $this->page->menuAddLink(t('Help'), 'help');
            $this->page->menuAddLink(t('Logout'), 'disconnect');

			$content = new TplPageBuilder($user);

			$this->page->setContent($content->build('main.tpl'));
			echo $this->page->build('page.tpl');
        }
	}

	function friend()
	{
        $user = new User();
        
        $cd = new \modl\ContactDAO();
        $contact = $cd->get($_GET['f']);

        if(isset($contact))
            $name = $contact->getTrueName();
        else
            $name = $_GET['f'];

		if(!$user->isLogged()) {
			$this->login();
		} else {
			if(isset($_GET['f']) && $_GET['f'] != "" ) {
				$this->page->setTitle(APP_TITLE.' - '.$name);
                $this->page->menuAddLink(t('Home'), 'main');
                $this->page->menuAddLink(t('Explore'), 'explore');
				$this->page->menuAddLink(t('Profile'), 'profile');
                $this->page->menuAddLink(t('Media'), 'media');
				$this->page->menuAddLink(t('Configuration'), 'config');
                $this->page->menuAddLink(t('Help'), 'help');
                $this->page->menuAddLink(t('Logout'), 'disconnect');

				$content = new TplPageBuilder($user);

				$this->page->setContent($content->build('friend.tpl'));
				echo $this->page->build('page.tpl');
			}
			else
				$this->main();
		}
	}

    function server()
	{
        $user = new User();

		if(!$user->isLogged()) {
			$this->login();
		} else {
			if(isset($_GET['s']) && $_GET['s'] != "" ) {
				$this->page->setTitle(APP_TITLE.' - Server');
                $this->page->menuAddLink(t('Home'), 'main');
                $this->page->menuAddLink(t('Explore'), 'explore');
				$this->page->menuAddLink(t('Profile'), 'profile');
                $this->page->menuAddLink(t('Media'), 'media');
				$this->page->menuAddLink(t('Configuration'), 'config');
                $this->page->menuAddLink(t('Help'), 'help');
                $this->page->menuAddLink(t('Logout'), 'disconnect');

				$content = new TplPageBuilder($user);

				$this->page->setContent($content->build('server.tpl'));
				echo $this->page->build('page.tpl');
			}
			else
				$this->main();
		}
	}
    
    function node()
	{
        $user = new User();

		if(!$user->isLogged()) {
			$this->login();
		} else {
			if(isset($_GET['n']) && $_GET['n'] != "" ) {
				$this->page->setTitle(APP_TITLE.' - Node');
                $this->page->menuAddLink(t('Home'), 'main');
                $this->page->menuAddLink(t('Explore'), 'explore');
				$this->page->menuAddLink(t('Profile'), 'profile');
                $this->page->menuAddLink(t('Media'), 'media');
				$this->page->menuAddLink(t('Configuration'), 'config');
                $this->page->menuAddLink(t('Help'), 'help');
                $this->page->menuAddLink(t('Logout'), 'disconnect');

				$content = new TplPageBuilder($user);

				$this->page->setContent($content->build('node.tpl'));
				echo $this->page->build('page.tpl');
			}
			else
				$this->main();
		}
	}
    
    function media()
	{
        $user = new User();

		if(!$user->isLogged()) {
			$this->login();
		} else {
            $this->page->setTitle(APP_TITLE.' - Node');
            $this->page->menuAddLink(t('Home'), 'main');
            $this->page->menuAddLink(t('Explore'), 'explore');
            $this->page->menuAddLink(t('Profile'), 'profile');
            $this->page->menuAddLink(t('Media'), 'media', true);
            $this->page->menuAddLink(t('Configuration'), 'config');
            $this->page->menuAddLink(t('Help'), 'help');
            $this->page->menuAddLink(t('Logout'), 'disconnect');

            $content = new TplPageBuilder($user);

            $this->page->setContent($content->build('media.tpl'));
            echo $this->page->build('page.tpl');
		}
	}

	function config()
	{
		$user = new User();

		if(!$user->isLogged()) {
			$this->login();
		} else {
			$this->page->setTitle(t('%s - Configuration', APP_TITLE));
            $this->page->menuAddLink(t('Home'), 'main');
            $this->page->menuAddLink(t('Explore'), 'explore');
            $this->page->menuAddLink(t('Profile'), 'profile');
            $this->page->menuAddLink(t('Media'), 'media');
			$this->page->menuAddLink(t('Configuration'), 'config', true);
            $this->page->menuAddLink(t('Help'), 'help');
            $this->page->menuAddLink(t('Logout'), 'disconnect');

			$content = new TplPageBuilder($user);

			$this->page->setContent($content->build('config.tpl'));
			echo $this->page->build('page.tpl');
		}
	}

	function profile()
	{
		$user = new User();

		if(!$user->isLogged()) {
			$this->login();
		} else {
			$this->page->setTitle(t('%s - Profile', APP_TITLE));
            $this->page->menuAddLink(t('Home'), 'main');
            $this->page->menuAddLink(t('Explore'), 'explore');
			$this->page->menuAddLink(t('Profile'), 'profile', true);
            $this->page->menuAddLink(t('Media'), 'media');
			$this->page->menuAddLink(t('Configuration'), 'config');
            $this->page->menuAddLink(t('Help'), 'help');
            $this->page->menuAddLink(t('Logout'), 'disconnect');

			$content = new TplPageBuilder($user);

			$this->page->setContent($content->build('profile.tpl'));
			echo $this->page->build('page.tpl');
		}
	}

	function account()
	{
        $this->page->setTitle(t('%s - Account', APP_TITLE));
        $this->page->menuAddLink(t('Home'), 'main');
        $this->page->menuAddLink(t('Account Creation'), 'account', true);
        $content = new TplPageBuilder();

        $this->page->setContent($content->build('account.tpl'));
        echo $this->page->build('page.tpl');
	}

	function blog()
	{
        $this->page->setTitle(t('%s - Account', APP_TITLE));
        $this->page->menuAddLink(t('Home'), 'main');
        $this->page->menuAddLink(t('Blog'), 'blog', true);
        $content = new TplPageBuilder();

        $this->page->setContent($content->build('blog.tpl'));
        echo $this->page->build('page.tpl');
	}

	function post()
	{
		$user = new User();

		if(!$user->isLogged()) {
			$this->login();
		} else {
			$this->page->setTitle(t('%s - Post View', APP_TITLE));
            $this->page->menuAddLink(t('Home'), 'main');
            $this->page->menuAddLink(t('Explore'), 'explore');
			$this->page->menuAddLink(t('Profile'), 'profile');
			$this->page->menuAddLink(t('Configuration'), 'config');
            $this->page->menuAddLink(t('Help'), 'help');
            $this->page->menuAddLink(t('Logout'), 'disconnect');

			$content = new TplPageBuilder($user);

			$this->page->setContent($content->build('post.tpl'));
			echo $this->page->build('page.tpl');
		}
	}

	/**
	 * Show login interface
	 */
	function login()
	{
    	$this->page->setTitle(t('%s - Login to Movim', APP_TITLE));
		$this->page->menuAddLink(t('Home'), 'main', true);

        $content = new TplPageBuilder($user);
		$this->page->setContent($content->build('login.tpl'));
		echo $this->page->build('page.tpl');
	}
    
	/**
	 * Create the Atom feed of a user
	 */
	function feed()
	{
        $content = new TplPageBuilder();
        echo $content->build('feed.tpl');
	}
    
	/**
	 * Explore the XMPP network
	 */
	function explore()
	{
		$user = new User();

        $this->page->setTitle(t('%s - Explore', APP_TITLE));

		if(!$user->isLogged()) {
            $this->login();
		} else {
            $this->page->menuAddLink(t('Home'), 'main');
            $this->page->menuAddLink(t('Explore'), 'explore', true);
            $this->page->menuAddLink(t('Profile'), 'profile');
            $this->page->menuAddLink(t('Media'), 'media');
			$this->page->menuAddLink(t('Configuration'), 'config');
            $this->page->menuAddLink(t('Help'), 'help');
            $this->page->menuAddLink(t('Logout'), 'disconnect');
		}

        $content = new TplPageBuilder($user);
        $this->page->setContent($content->build('explore.tpl'));
        echo $this->page->build('page.tpl');
	}
    

    /*
     * Show help page
     */
     function help()
     {
		$user = new User();


        $this->page->setTitle(t('%s - Help Page', APP_TITLE));

		if(!$user->isLogged()) {
            $this->login();
		} else {
            $this->page->menuAddLink(t('Home'), 'main');
            $this->page->menuAddLink(t('Explore'), 'explore');
            $this->page->menuAddLink(t('Profile'), 'profile');
            $this->page->menuAddLink(t('Media'), 'media');
			$this->page->menuAddLink(t('Configuration'), 'config');
            $this->page->menuAddLink(t('Help'), 'help', true);
            $this->page->menuAddLink(t('Logout'), 'disconnect');
		}

        $content = new TplPageBuilder($user);
        $this->page->setContent($content->build('help.tpl'));
        echo $this->page->build('page.tpl');

     }

	function disconnect()
	{
		$user = new User();
		$user->desauth();
		$this->login();
	}
}
