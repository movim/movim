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
        $this->registerEvent('config', 'onConfig');
    }
    
    function onConfig(array $data)
    {
        $this->user->setConfig($data);
    }

	function ajaxSubmit($data) {
        $config = $this->user->getConfig();
        if(isset($config))
            $data = array_merge($config, $data);
        
        $s = new moxl\StorageSet();
        $s->setXmlns('movim:prefs')
          ->setData(serialize($data))
          ->request();
	}

	function ajaxGet() {
        $s = new moxl\StorageGet();
        $s->setXmlns('movim:prefs')
          ->request();
	}

	function build()
	{
            $languages = load_lang_array();
            /* We load the user configuration */
            $conf = $this->user->getConfig('language');

            $submit = $this->genCallAjax('ajaxSubmit', "movim_parse_form('general')")
                . "this.className='button icon loading'; setTimeout(function() {location.reload(true)}, 2000);";
    ?>
        <div id="config">
            <form enctype="multipart/form-data" method="post" action="index.php" name="general">
                <div class="element">
                <label id="lock" for="language"><?php echo t('Language'); ?></label>
                <div class="select">
                <select name="language" id="language">
                    <option value="en">English (default)</option>
    <?php
                  foreach($languages as $key => $value ) {
                     if($key == $conf) { ?>
                        <option value="<?php echo $key; ?>" selected="selected"><?php echo $value; ?></option>
    <?php		       	 } else {?>
                        <option value="<?php echo $key; ?>"><?php echo $value; ?></option>
    <?php			     }
                  } ?>
                </select>
                </div>
                </div>
                <hr />
    <!--<label id="lock" for="soundnotif"><?php echo t('Enable Sound Notification:'); ?></label>
              <input type="checkbox" name="soundnotif" value="soundnotif" checked="checked" /><br /> -->
    <!--<input value="<?php echo t('Submit'); ?>" onclick="<?php echo $submit; ?>" type="button" class="button icon yes merged right" style="float: right;">
                <input type="reset" value="<?php echo t('Reset'); ?>" class="button icon no merged left" style="float: right;">-->

                <br />
                <a onclick="<?php echo $submit; ?>" type="button" class="button icon yes" style="float: right;"><?php echo t('Submit'); ?></a>
                <!--<a type="reset" value="<?php echo t('Reset'); ?>" class="button icon no merged left" style="float: right;">-->
                </p>
            </form>
            <br /><br />
            <div class="message info"><?php echo t("This configuration is shared wherever you are connected !");?></div>
        </div>
<?php
	}

}

