<?php

/**
 * @package Widgets
 *
 * @file Chat.php
 * This file is part of MOVIM.
 * 
 * @brief The account creation
 *
 * @author Timothée Jaussoin <edhelas_at_g m a i l dot com>
 *
 * @version 1.0
 * @date 20 October 2010
 *
 * Copyright (C)2010 MOVIM project
 * 
 * See COPYING for licensing information.
 */

class Account extends WidgetBase
{
	private $user;
	
	function __construct(&$user)
	{
		$this->user = $user;
	}
	
	function build()
	{
			$form = new Form();
			$form->startForm(basename($_SERVER['PHP_SELF']));
				$form->startFieldset(t('Jabber Account'));
				$form->newline = true;
					$form->textInput('account',t('Jabber Account'),false,'block required');
					$form->textInput('password',t('Password'),false,'block required');
				$form->newline = true;
				$form->closeFieldset();
				$form->insertBR();
				$form->newline = false;
			    $form->submitButton(false, t('Submit'));
			    $form->newline = true;
			    $form->resetButton(false, t('Reset'));
			$form->closeForm();

			if(!$output = $form->getForm()) { die("error: " . $form->error); }
			else { echo $output; }
	}
}

?>
