<?php

/**
 * @package Widgets
 *
 * @file PubsubSubscriptionConfig.php
 * This file is part of MOVIM.
 *
 * @brief The Group configuration widget
 *
 * @author Ho Christine <nodpounod@gmail.com>
 *
 * @version 1.0
 * @date 24 March 2013
 *
 * Copyright (C)2010 MOVIM project
 *
 * See COPYING for licensing information.
 */

class PubsubSubscriptionConfig extends WidgetBase
{

    function WidgetLoad()
    {
        $this->registerEvent('groupsubscribedlist', 'onGroupSubscribedList');
        $this->registerEvent('groupadded', 'onGroupAdded');
        $this->registerEvent('groupremoved', 'onGroupRemoved');
    }

    function display()
    {
        $this->view->assign(
                    'getsubscribedlist',
                    $this->genCallAjax('ajaxGetGroupSubscribedList')
                    );
    }
    
    function onGroupSubscribedList($list) {
        $html = $this->prepareList($list);
        RPC::call('movim_fill', 'groupsubscribedlistconfig', $html); 
    }
    
    function prepareList($list) {
        $configlist = $this->tpl();
        $sd = new \modl\SubscriptionDAO();

        $listhtml = '';
        
        //if($sd != null && $sd->getSubscribed() != null) {
            foreach($sd->getSubscribed() as $s) {
                if($s->name != null)
                    $name = $s->name;
                else
                    $name = $s->node;

                if(isset($list[$s->server.$s->node]))
                    $checked = 'checked';
                else
                    $checked = '';

                $switch = $this->genCallAjax(
                            'ajaxChangeSubscribed',
                            "'".$s->server."'",
                            "'".$s->node."'",
                            "this.checked",
                            "'".$name."'");

                $listhtml .= '
                    <li>
                        <a class="action">
                            <div class="checkbox">
                                <input
                                    type="checkbox"
                                    id="privacy'.$s->node.'"
                                    name="privacy'.$s->node.'"
                                    '.$checked.'
                                    onchange="'.$switch.'"/>
                                <label for="privacy'.$s->node.'"></label>
                            </div>
                        </a>
                        <a href="'.Route::urlize('node', array($s->server, $s->node)).'">'.
                            $name.' 
                        </a>
                    </li>';
            }

            $configlist->assign('list',       $listhtml);

            return $configlist->draw('_pubsubsubscriptionconfig_list', true);
        //} else return t('No public groups found');
    }
    
    function onGroupAdded($node) {
        Notification::appendNotification(t('%s has been added to your public groups', $node), 'success');
        RPC::commit(); 
    }
    
    function onGroupRemoved($node) {
        Notification::appendNotification(t('%s has been removed from your public groups', $node), 'success');
        RPC::commit(); 
    }

    function ajaxChangeSubscribed($server, $node, $state, $name) {
        $data = array('title' => $name);
        
        if($state) {
            $r = new moxl\PubsubSubscriptionListAdd();
            $r->setNode($node)
              ->setTo($server)
              ->setFrom($this->user->getLogin())
              ->setData($data)
              ->request();
        } else {
            $r = new moxl\PubsubSubscriptionListRemove();
            $r->setNode($node)
              ->setTo($server)
              ->setFrom($this->user->getLogin())
              ->request();
        }
    }

    function ajaxGetGroupSubscribedList(){
        $r = new moxl\PubsubSubscriptionListGet();
        $r->request();
    }

}
