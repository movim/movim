<?php

class EventHandler
{
	private $conf;

	function __construct()
	{
		$this->conf = new GetConf();
	}

	function runEvent($type, $event)
	{
        global $polling;
        if(!$polling) { // avoids issues while loading pages.
            return;
        }

        $widgets = WidgetWrapper::getInstance(false);
        ob_clean();
        $widgets->iterate('runEvents', array(
                              'type' => 'allEvents',
                              'data' => $event
                              ));
        $widgets->iterate('runEvents', array(
                              'type' => $type,
                              'data' => $event
                              ));

        $payload = ob_get_clean();
        if(trim(rtrim($payload)) != "") {
            header('Content-Type: text/xml');
            echo '<?xml version="1.0" encoding="UTF-8" ?>'
            .'<movimcontainer>'
            .$payload
            .'</movimcontainer>';
        }
	}
}

?>
