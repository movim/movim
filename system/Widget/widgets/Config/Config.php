<?php

/**
 * @package Widgets
 *
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

class Config extends WidgetBase
{
    function WidgetLoad()
    {
    
    }
	
	function ajaxSubmit($data) {
		$usr = new User();
		$usr->setConf($data);
	}
	
	function build()
	{
			$languages = load_lang_array();
			/* We load the user configuration */
			$usr = new User();
			$conf = $usr->getConf();
			
			$submit = $this->genCallAjax('ajaxSubmit', "movim_parse_form('general')") . 'location.reload(true);';
?>

			<form enctype="multipart/form-data" method="post" action="index.php" name="general">

				<label id="lock" for="language"><?php echo t('Language'); ?></label>
				<select name="language" id="language">
					<option value="en">English (default)</option>
<?php
			   	  foreach($languages as $key => $value ) {
			   	  	 if($key == $conf['language']) { ?>
			   	  	 	<option value="<?php echo $key; ?>" selected="selected"><?php echo $value; ?></option>
<?php		     	 } else {?>
			   	  	 	<option value="<?php echo $key; ?>"><?php echo $value; ?></option>
<?php			     }
				  } ?>
				</select>
				<?php /*<br>
				<br>
				<fieldset>
				<legend><?php echo t('BOSH Connection Prefrences'); ?></legend>
				<div class="warning"><?php echo t('Changing these data can be dangerous and may compromise the connection to the Jabber server'); ?></div>
                <p>
				    <label id="lock required" for="boshHost"><?php echo t('Bosh Host'); ?></label>
				    <input name="boshHost" id="boshHost" value="<?php echo $conf['boshHost']; ?>" type="text">
				</p>
                <p>
				<label id="lock required" for="boshSuffix"><?php echo t('Bosh Suffix'); ?></label>
				<input name="boshSuffix" id="boshSuffix" value="<?php echo $conf['boshSuffix']; ?>" type="text">
				</p>
				<label id="lock required" for="boshPort"><?php echo t('Bosh Port'); ?></label>
				<input name="boshPort" id="boshPort" size="4" value="<?php echo $conf['boshPort']; ?>" type="text">
				</fieldset>
				<p>*/ ?>
				<hr />
				<input value="<?php echo t('Submit'); ?>" onclick="<?php echo $submit; ?>" type="button" class="button icon yes merged right" style="float: right;">
				<input type="reset" value="<?php echo t('Reset'); ?>" class="button icon no merged left" style="float: right;">
                </p>
			</form>

<?php
	}
	
}

?>
