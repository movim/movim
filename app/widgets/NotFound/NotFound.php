<?php
 
class NotFound extends WidgetBase
{
    function load()
    {
        $this->addcss('notfound.css');
    }

    function display()
    {
        $this->view->assign('base_uri', BASE_URI);
    }
}
