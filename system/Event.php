<?php

class Event
{
    private $conf;

    function __construct()
    {
        $this->conf = new Conf();
    }

    function runEvent($type, $event = false)
    {
        global $polling;
        if(!$polling) { // avoids issues while loading pages.
            return;
        }

        $widgets = WidgetWrapper::getInstance(false);

        $widgets->iterate('runEvents', array(
                              array(
                                  'type' => $type,
                                  'data' => $event,
                                  )));

        // Outputting any RPC calls.
        RPC::commit();
    }
}

?>
