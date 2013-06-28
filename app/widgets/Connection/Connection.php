<?php

/**
 * @package Widgets
 *
 * @file Connection.php
 * This file is part of MOVIM.
 * 
 * @version 1.0

 * Copyright (C)2013 MOVIM project
 * 
 * See COPYING for licensing information.
 */

class Connection extends WidgetBase
{
	function WidgetLoad()
	{
    	$this->addcss('connection.css');
        $this->registerEvent('connection', 'onConnection');
	}
    
    function ajaxSetPresence()
    {

    }
    
    function onConnection($value)
    {
        if($value <= 10)
            RPC::call('movim_redirect', Route::urlize('main'));
        else {
            $value = floor(($value-10)/10);
            RPC::call('movim_fill', 'countdown', $value);   
        }
        RPC::commit();
    }

    function build()
    {
    ?>
        <script type="text/javascript">
            setTimeout("<?php //echo $this->callAjax('ajaxSetPresence');?>", 1000);
        </script>
        <div id="connection">
            <?php echo t('Loading your session...'); ?>
            <div id="countdown"></div>
        </div>
    <?php
    }
}

?>
