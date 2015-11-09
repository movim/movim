<?php

class Event
{
    function runEvent($key, $data = null)
    {
        $widgets = WidgetWrapper::getInstance();
        $widgets->iterate($key, $data);
    }
}

?>
