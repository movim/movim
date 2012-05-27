<?php

/**
 * @package Widgets
 *
 * @file Log.php
 * This file is part of MOVIM.
 * 
 * @brief The log widget.
 *
 * @author Timothée Jaussoin <edhelas_at_gmail_dot_com>
 *
 * @version 1.0
 * @date 20 October 2010
 *
 * Copyright (C)2010 MOVIM project
 * 
 * See COPYING for licensing information.
 */

class Log extends WidgetBase
{
    function widgetLoad()
    {
        $this->addcss('log.css');
        $this->registerEvent('allEvents', 'onEvent');
    }

	function build()
	{
		?>
		<div id="log">
		  <h3><?php echo t('Debug console'); ?></h3>

          <div id="log_content">
          </div>
       	</div>
		<?php
	}

    function onEvent($data)
    {
/*        RPC::call('movim_prepend',
                             'log_content',
                             RPC::cdata("<span>%s&gt; data : </span>%s<br />",
                                             date('H:i:s'),
                                             var_export($data, true)));*/
    }
}

?>
