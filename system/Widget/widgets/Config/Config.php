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
		$this->addcss('config.css');
    }

	function ajaxSubmit($data) {
		$usr = new User();
        $usr->setLang($data['language']);
	}

	function build()
	{
			$languages = load_lang_array();
			/* We load the user configuration */
			$conf = UserConf::getConf();

			$submit = $this->genCallAjax('ajaxSubmit', "movim_parse_form('general')")
                . "this.className='button icon loading merged right'; setTimeout(function() {location.reload(true)}, 2000);";
?>
		<div id="config">
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
				<hr />
               <label id="lock" for="soundnotif"><?php echo t('Enable Sound Notification:'); ?></label>
              <input type="checkbox" name="soundnotif" value="soundnotif" checked="checked" /><br /> 
				<input value="<?php echo t('Submit'); ?>" onclick="<?php echo $submit; ?>" type="button" class="button icon yes merged right" style="float: right;">
				<input type="reset" value="<?php echo t('Reset'); ?>" class="button icon no merged left" style="float: right;">
                </p>
			</form>
		</div>
<?php
	}

}

?>
