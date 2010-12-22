<?php

/**
 * @file Wall.php
 * This file is part of MOVIM.
 * 
 * @brief The configuration form
 *
 * @author Timothée Jaussoin <edhelas_at_gmail_dot_com>
 *
 * @version 1.0
 * @date 28 October 2010
 *
 * Copyright (C)2010 MOVIM project
 * 
 * See COPYING for licensing information.
 */

class Config extends Widget
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

			/* Note that the select fields aren't translated and the languages
			 * are in their native form. This should not be changed.*/
			   $form->startSelect('language',t('Language'),false,'block');
			      $form->addOption('en_gb', 'English(UK)');
			      $form->addOption('en_gb', 'Français(France)');
			   $form->closeSelect();
			   $form->insertBR();

			   $form->textInput('name',t('Full Name'),false,'block required');
			   $form->startFieldset(t('Gender'));
				  $form->newline = true;
				  $form->checkboxInput('radio','gender','male',t('Male'));
				  $form->checkboxInput('radio','gender','female',t('Female'));
				  $form->newline = true;
			   $form->closeFieldset();
			   $form->startFieldset(t('Interests'));
				  $form->checkboxInput('checkbox','lazy','lazy',t('Movies'));
				  $form->checkboxInput('checkbox','intellectual','intellectual',t('Reading'));
				  $form->checkboxInput('checkbox','jock','jock',t('Sports'));
			   $form->closeFieldset();
			   $form->fileInput('picture',t('Upload Your Picture'));
			   $form->textareaInput('about',t('About You'),false,'block');
			   $form->startSelect('age',t('Your Age'),false,'block');
				  $form->addOption('0-3', t('baby'));
				  $form->addOption('3-5', t('toddler'));
				  $form->addOption('5-12', t('child'));
				  $form->addOption('13-19', t('teen'));
				  $form->addOption('20-45', t('adult'));
				  $form->addOption('45-65', t('middleage'));
				  $form->addOption('65-75', t('retiree'));
				  $form->addOption('75-95', t('old'));
				  $form->addOption('100+', t('stillalive'));
			   $form->closeSelect();
			   $form->insertBR();
			   $form->newline = false;
			   $form->submitButton();
			   $form->newline = true;
			   $form->resetButton();
			$form->closeForm();

			if(!$output = $form->getForm()) {
				throw new MovimException(t("error: ") . $form->error);
			} else {
				echo $output;
			}
	}
}

?>
