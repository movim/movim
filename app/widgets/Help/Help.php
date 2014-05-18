<?php

/**
 * @package Widgets
 *
 * @file Vcard.php
 * This file is part of MOVIM.
 * 
 * @brief A widget which display some help 
 *
 * @author Timothée    Jaussoin <edhelas_at_gmail_dot_com>
 *
 * @version 1.0
 * @date 3 may 2012
 *
 * Copyright (C)2010 MOVIM project
 * 
 * See COPYING for licensing information.
 */

class Help extends WidgetBase
{
    function load() 
    {
    }
    
    function display() 
    {
        $this->view->assign('missing_info1', 
            $this->__(
                'missing.info1', 
                '<a href="http://wiki.movim.eu/en:dev:roadmaps" target="_blank">', 
                '</a>'));
        $this->view->assign('missing_info2', 
            $this->__(
                'missing.info2', 
                '<a href="http://wiki.movim.eu/whoami#how_can_i_help" target="_blank">', 
                '</a>'));
        $this->view->assign('faq_info1', 
            $this->__(
                'faq.info1', 
                '<a href="http://wiki.movim.eu/whoami#faq" target="_blank">', 
                '</a>', 
                '<a href="xmpp:movim@conference.movim.eu" target="_blank">movim@conference.movim.eu</a>', 
                '<a href="http://wiki.movim.eu/en:mailing_list" target="_blank">', 
                '</a>'));
    }
}

