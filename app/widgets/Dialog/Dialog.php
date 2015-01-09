<?php

class Dialog extends WidgetBase
{
    function load() 
    {
        $this->addjs('dialog.js');
    }

    static function fill($html = '', $scroll = false)
    {
        if($scroll) {
            RPC::call('Dialog.toggleScroll');
        }
        RPC::call('movim_fill', 'dialog', $html);
    }
    
    function display() 
    {
    }
}
