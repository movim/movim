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

use Moxl\Xec\Action\Roster\UpdateItem;

class ContactManage extends WidgetCommon
{
    function load() 
    {
        $this->registerEvent('rosterupdateditem', 'onRoster');
    }
    
    function display()
    {
        $this->view->assign('ok', ($_GET['f'] != $this->user->getLogin()));
        $this->view->assign('contact', $this->prepareContactManage($_GET['f']));
    }

    public function onRoster($jid)
    {
        $html = $this->prepareContactManage($jid);
        Notification::appendNotification($this->__('contact.updated'));
        RPC::call('movim_fill', 'contactmanage', $html);
    }
    
    public function ajaxContactManage($form) 
    {
        $rd = new UpdateItem;
        $rd->setTo(echapJid($form['jid']))
           ->setFrom($this->user->getLogin())
           ->setName(htmlspecialchars($form['alias']))
           ->setGroup(htmlspecialchars($form['group']))
           ->request();
    }
    
    private function prepareContactManage($jid) 
    {
        $rd = new \Modl\RosterLinkDAO();
        $groups = $rd->getGroups();
        $rl     = $rd->get($jid);
        
        $html = '';

        if(isset($rl)) {
            $form = $this->tpl();
            $form->assign('submit', 
                $this->genCallAjax(
                    'ajaxContactManage', 
                    "movim_parse_form('manage')"));
            $form->assign('rl', $rl);
            $form->assign('groups', $groups);
            $html = $form->draw('_contact_manage_form', true);
        
            /*
            $submit = ;
            
            $html .= '<h2>'.t('Manage').'</h2>';
            
            $html .= '
                <form name="manage">';
                
            $ghtml = '';

            if(is_array($groups)) {
                foreach($groups as $g)
                    $ghtml .= '<option value="'.$g.'"/>';
            }
                
            $html .= '
                    <input type="hidden" name="jid" value="'.$jid.'"/>
                    <div class="element large mini">
                        <input name="alias" id="alias" class="tiny" placeholder="'.t('Alias').'" value="'.$rl->rostername.'"/>
                    </div>
                    <div class="element large mini">
                        <datalist id="group" style="display: none;">
                            '.$ghtml.'
                        </datalist>
                        <input name="group" list="group" id="alias" class="tiny" placeholder="'.t('Group').'" value="'.$rl->groupname.'"/>
                    </div>
                    
                    <a name="submit" class="button black icon yes" onclick="'.$submit.' this.style.display = \'none\';">'.t('Save').'</a>';
                
            $html .= '
                </form>';*/
        }
        
        return $html;
    }    
}
