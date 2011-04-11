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
    function WidgetLoad()
    {
    
    }
	
	function ajaxSubmit($data) {
		$usr = new User();
		$conf = Conf::setUserConf($usr->getLogin(), $data);
	}
	
	function build()
	{
			$languages = load_lang_array();
			/* We load the user configuration */
			$usr = new User();
			$conf = Conf::getUserConf($usr->getLogin());
			
			$submit = $this->genCallAjax('ajaxSubmit', "movim_parse_form('general')");
			
			$form = new Form();
			$form->startForm(basename($_SERVER['PHP_SELF']), false, false, 'post', 'general');

			/* Note that the select fields aren't translated and the languages
			 * are in their native form. This should not be changed.*/
			   $form->startSelect('language',t('Language'),false,'block');
			   	  $form->addOption('en', 'English (default)');
			   	  foreach($languages as $key => $value ) {
			   	  	 if($key == $conf['language'])
			     	 	$form->addOption($key, $value, true);
			     	 else
			     	 	$form->addOption($key, $value);
			      }
			   $form->closeSelect();
			   $form->insertBR();
			   $form->insertBR();
			   
			   $form->startFieldset(t('BOSH Connection Prefrences'));
			   $form->insertHTML('<div class="warning">'.
						   		t('Changing these data can be dangerous and may compromise the connection to the XMPP server')
						   		.'</div>');
			   $form->textInput('boshHost',t('Bosh Host'),false,'block required', false, false, false, false, $conf['boshHost']);
			   $form->insertBR();
			   $form->textInput('boshSuffix',t('Bosh Suffix'),false,'block required', false, false, false, false, $conf['boshSuffix']);
			   $form->insertBR();
			   $form->textInput('boshPort',t('Bosh Port'),false,'block required', false, false, 4, false, $conf['boshPort']);
			   $form->closeFieldset();

			   $form->insertBR();
			   $form->newline = false;
			   $form->genericButton($submit, t('Submit'), '.right');
			   $form->newline = true;
			   $form->resetButton(false, t('Reset'));
			$form->closeForm();

			if(!$output = $form->getForm()) {
				throw new MovimException(t("error: ") . $form->error);
			} else {
				echo $output;
			}
	}
	
}

?>
