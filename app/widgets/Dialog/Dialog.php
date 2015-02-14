<?php

class Dialog extends WidgetBase
{
    function load() 
    {
        $this->addjs('dialog.js');
    }

    static function fill($html = '', $scroll = false)
    {
        RPC::call('movim_fill', 'dialog', $html);

        if($scroll) {
            RPC::call('Dialog.addScroll');
        }
    }
    
    function display() 
    {
    }
}
