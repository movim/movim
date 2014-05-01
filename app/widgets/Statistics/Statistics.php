<?php

/**
 * @package Widgets
 *
 * @file Statistics.php
 * This file is part of MOVIM.
 *
 * @brief The administration widget.
 *
 * @author Timothée Jaussoin <edhelas@gmail.com>
 * *
 * Copyright (C)2014 MOVIM project
 *
 * See COPYING for licensing information.
 */

use Modl\SessionxDAO;
 
class Statistics extends WidgetBase {
    function load() {
        $sd = new SessionxDAO;
        $this->view->assign('sessions',      $sd->getAll());
    }

    function getTime($date) {
        return prepareDate(strtotime($date));
    }
}
