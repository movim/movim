<?php

class Event
{
    private $conf;
    static $_grouped_events;

    function __construct()
    {
        $this->conf = new \system\Conf();
        if(self::$_grouped_events == null)
            self::$_grouped_events = array();
    }

    function runEvent($type, $event = false)
    {
        global $polling;
        if(!$polling) { // avoids issues while loading pages.
            return;
        }
        
        if(!$event) 
            array_push(self::$_grouped_events, $type);
        else {
            $widgets = WidgetWrapper::getInstance(false);

    /*        $widgets->iterate('runEvents', array(
                                  array(
                                      'type' => 'allEvents',
                                      'data' => $event,
                                      )));*/
            $widgets->iterate('runEvents', array(
                                  array(
                                      'type' => $type,
                                      'data' => $event,
                                      )));
                                  
        /*$widgets->iterateAll('isEvents', array(
                              array(
                                  'type' => $type,
                                  'data' => $event,
                                  )));*/
        
        // Outputting any RPC calls.
            RPC::commit();
        }
    }
    
    function launchEvents() {
        $events = array_unique(self::$_grouped_events);
        $widgets = WidgetWrapper::getInstance(false);

        foreach($events as $e) {
            $widgets->iterate('runEvents', array(
                                  array(
                                      'type' => $e,
                                      'data' => false,
                                      )));
        }
        
        RPC::commit();
    }
}

?>
