<?php

class About extends \Movim\Widget\Base
{
    public function display()
    {
        $this->view->assign('version', APP_VERSION);
    }
}
