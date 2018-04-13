<?php

class About extends \Movim\Widget\Base
{
    function display()
    {
        $this->view->assign('version', APP_VERSION);
    }
}
