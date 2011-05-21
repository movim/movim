<?php

/**
 * @package Widgets
 *
 * @file Chat.php
 * This file is part of MOVIM.
 *
 * @brief A jabber chat widget.
 *
 * @author Guillaume Pasquet <etenil@etenilsrealm.nl>
 *
 * @version 1.0
 * @date 20 October 2010
 *
 * Copyright (C)2010 MOVIM project
 *
 * See COPYING for licensing information.
 */

class Wall extends WidgetBase
{

    function WidgetLoad()
    {
    	$this->addcss('wall.css');
    }

	function build()
	{
		?>
		<div id="wall">
		    <div class="message">
				<img class="avatar" width="70px" height="70px" alt="test">

		    	<div class="content">
 			<?php echo t("This is my custom Wall widget.");?>
		    	</div>
		    	<div class="comment">
		    	Commentaire 1
		    	</div>
       		</div>
       	</div>
		<?php
	}
}

?>
