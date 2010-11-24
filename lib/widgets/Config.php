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
			   $form->textInput('name','Full Name',false,'block required');
			   $form->startFieldset('Gender');
				  $form->newline = true;
				  $form->checkboxInput('radio','gender','male','Male');
				  $form->checkboxInput('radio','gender','female','Female');
				  $form->newline = true;
			   $form->closeFieldset();
			   $form->startFieldset('Interests');
				  $form->checkboxInput('checkbox','lazy','lazy','Movies');
				  $form->checkboxInput('checkbox','intellectual','intellectual','Reading');
				  $form->checkboxInput('checkbox','jock','jock','Sports');
			   $form->closeFieldset();
			   $form->fileInput('picture','Upload Your Picture');
			   $form->textareaInput('about','About You',false,'block');
			   $form->startSelect('age','Your Age',false,'block');
				  $form->addOption('baby','0-3');
				  $form->addOption('toddler','3-5');
				  $form->addOption('child','5-12');
				  $form->addOption('teen','13-19');
				  $form->addOption('adult','20-45');
				  $form->addOption('middleage','45-65');
				  $form->addOption('retiree','65-75');
				  $form->addOption('old','75-95');
				  $form->addOption('stillalive','100+');
			   $form->closeSelect();
			   $form->insertBR();
			   $form->newline = false;
			   $form->submitButton();
			   $form->newline = true;
			   $form->resetButton();
			$form->closeForm();

			if(!$output = $form->getForm()) { die("error: " . $form->error); }
			else { echo $output; }
	}
}

?>
