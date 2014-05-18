<?php

/**
 * @package Widgets
 *
 * @file MediaUpload.php
 * This file is part of MOVIM.
 *
 * @brief The media upload.
 *
 * @author Timothée Jaussoin <edhelas@gmail.com>
 *
 * @version 1.0
 * @date 07 December 2011
 *
 * Copyright (C)2010 MOVIM project
 *
 * See COPYING for licensing information.
 */

class MediaUpload extends WidgetBase {
    function load() {
        $this->addcss('mediaupload.css');
    }
    
    function display() {
        if($this->user->dirSize() < $this->user->sizelimit)
            $this->view->assign('limit', true);
        else
            $this->view->assign('limit', false);
    }
}
