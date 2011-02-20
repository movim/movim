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
        file_put_contents('event', "Running $type.\n");
        
        global $polling;
        if(!$polling) { // avoids issues while loading pages.
            return;
        }

        $widgets = WidgetWrapper::getInstance(false);

        $rpc = new MovimRPC_Exec();
        
        $all = $widgets->iterate('runEvents', array(
                                             array(
                                                 'type' => 'allEvents',
                                                 'data' => $event,
                                                 )));
        $ev = $widgets->iterate('runEvents', array(
                                             array(
                                                 'type' => $type,
                                                 'data' => $event,
                                                 )));
        $rpc->addCalls($all);
        $rpc->addCalls($ev);
        
        $rpc->exec();

        file_put_contents('all', var_export($all, true));
        file_put_contents('ev', var_export($ev, true));
	}
}

?>
