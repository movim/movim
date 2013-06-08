<?php

/**
 * @package Widgets
 *
 * @file ContactManage.php
 * This file is part of MOVIM.
 *
 * @brief A little widget which manage the current contact
 *
 * @author Jaussoin Timothée <edhelas@gmail.com>
 *
 * @version 1.0
 * @date 24 March 2013
 *
 * Copyright (C)2013 MOVIM project
 *
 * See COPYING for licensing information.
 */

class ContactManage extends WidgetCommon
{
    function WidgetLoad() {
        
    }
    
    public function ajaxContactManage($form) {
        $rd = new \moxl\RosterUpdateItem();
        $rd->setTo(echapJid($form['jid']))
           ->setName(htmlspecialchars($form['alias']))
           ->setGroup(htmlspecialchars($form['group']))
           ->request();
        Notification::appendNotification(t('Contact updated'));
    }
    
    private function prepareContactManage($jid) {
        $rd = new \modl\RosterLinkDAO();
        
        $groups = $rd->getGroups();
        $rl = $rd->get($jid);
        
        $submit = $this->genCallAjax('ajaxContactManage', "movim_parse_form('manage')");
        
        $html = '';
        
        $html .= '<h2>'.t('Manage').'</h2>';
        
        $html .= '
            <form name="manage">';
            
        $ghtml = '';
        foreach($groups as $g)
            $ghtml .= '<option value="'.$g.'"/>';
            
        $html .= '
                <input type="hidden" name="jid" value="'.$jid.'"/>
                <div class="element large mini">
                    <input name="alias" id="alias" class="tiny" placeholder="'.t('Alias').'" value="'.$rl->rostername.'"/>
                </div>
                <div class="element large mini">
                    <datalist id="group" style="display: none;">
                        '.$ghtml.'
                    </datalist>
                    <input name="group" list="group" id="alias" class="tiny" placeholder="'.t('Group').'" value="'.$rl->group.'"/>
                </div>
                
                <a class="button black icon yes" onclick="'.$submit.'">'.t('Save').'</a>';
            
        $html .= '
            </form>';
        
        return $html;
    }
    
    function build() {
        ?>
        <div class="clear"></div>
        <?php
        if($_GET['f'] != $this->user->getLogin())
            echo $this->prepareContactManage($_GET['f']);        
    }
    
}
