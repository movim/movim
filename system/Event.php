<?php

class Event
{
    function runEvent($key, $data = null)
    {
        $widgets = WidgetWrapper::getInstance(false);
        $widgets->iterate($key, $data);
    }
}

?>
