<?php

/**
 * @package Widgets
 *
 * @file About.php
 * This file is part of MOVIM.
 * 
 * @brief A widget which display some help 
 *
 * @author Timothée
 * 
 * See COPYING for licensing information.
 */

class About extends \Movim\Widget\Base
{
    function load()
    {
    }

    function display()
    {
        $this->view->assign('version', APP_VERSION);
    }
}
