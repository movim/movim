<?php

class Dialog extends WidgetBase
{
    function load() 
    {
        $this->addjs('dialog.js');
    }

    static function fill($html = '')
    {
        RPC::call('movim_fill', 'dialog', $html);
    }
    
    function display() 
    {
    }
}
