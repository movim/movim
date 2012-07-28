<?php

/**
 * @package Widgets
 *
 * @file Vcard.php
 * This file is part of MOVIM.
 * 
 * @brief A widget which display some help 
 *
 * @author Timothée	Jaussoin <edhelas_at_gmail_dot_com>
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
    function WidgetLoad() {
        $this->addcss('help.css');
    }
    
    function build()
    {
        ?>
            <div id="help">
                
            <h2><?php echo t('What is Movim?'); ?></h2>
            
            <p><?php echo t('Visit the page %s What is Movim ? %s to know more about the project, its aims and understand how it works.', 
            '<a href="http://wiki.movim.eu/fr:whoami" target="_blank">', '</a>'); ?></p>
            
            <h2><?php echo t('What do the little banners refer to ?'); ?></h2>
            <center>    
            <div style="width: 60px; height: 50px; display: inline-block;" class="protect white"></div>
            <div style="width: 60px; height: 50px; display: inline-block;" class="protect green"></div>
            <div style="width: 60px; height: 50px; display: inline-block;" class="protect orange"></div>
            <div style="width: 60px; height: 50px; display: inline-block;" class="protect red"></div>
            <div style="width: 60px; height: 50px; display: inline-block;" class="protect black"></div>
            </center>
                
            <p><?php echo t('Thanks to these five little banners, you can quickly identitfy the level of confdentiality applied to the information you provide.'); ?></p>
            
            <p>
                <ul class="clean">
                    <li><?php echo t('White, only you can see the information'); ?></li>
                    <li><?php echo t('Green, you have chosen some contacts who can see your information'); ?></li>
                    <li><?php echo t('Orange, all your contact list can see your information'); ?></li>
                    <li><?php echo t('Red, everybody in the XMPP network can see your information'); ?></li>
                    <li><?php echo t('Black, the whole Internet can see your information'); ?></li>
                </ul>
            </p>
            
            <h2><?php echo t("Some features are missing/I can't do everything I used to do on other social networks"); ?></h2>

            <p><?php echo t("Although Movim is evolving fast, many (many) features are missing. Be patient ;). You can have a look %s at next versions's roadmaps %s to know if the one you want is on its way.", '<a href="http://wiki.movim.eu/fr:roadmaps" target="_blank">', '</a>'); ?>
            
            <?php echo t("Don't forget that Movim is an open source project, a helping hand is always welcome (see %s Can I participate %s)", 
            '<a href="http://wiki.movim.eu/fr:whoami#puis-je_participer" target="_blank">', '</a>'); ?></p>
            
            <h2><?php echo t("I can't find the answer to my question here"); ?></h2>
            
            <p><?php echo t('Go to the %s to the Frequently Asked Questions %s or come ask your question on the official chatroom %s or via our mailing-list (%s see the dedicated page %s).', 
            '<a href="http://wiki.movim.eu/fr:whoami#foire_aux_questions" target="_blank">', '</a>', 
            '<a href="xmpp:movim@conference.movim.eu" target="_blank">movim@conference.movim.eu</a>',
            '<a href="http://wiki.movim.eu/fr:mailing_list" target="_blank">', '</a>'); ?></p>
            
            </div>
        <?php
    }
}
