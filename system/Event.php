<?php
use Movim\Widget\Wrapper;

class Event
{
    function runEvent($key, $data = null)
    {
        $widgets = Wrapper::getInstance();
        $widgets->iterate($key, $data);
    }
}

?>
