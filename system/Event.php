<?php

class Event
{
    function runEvent($type, $event = false)
    {
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
