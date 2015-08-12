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

use Moxl\Xec\Action\PubsubSubscription\ListGet;
use Moxl\Xec\Action\PubsubSubscription\ListAdd;
use Moxl\Xec\Action\PubsubSubscription\ListRemove;

class PubsubSubscriptionConfig extends WidgetBase
{

    function load()
    {
        $this->registerEvent('groupsubscribedlist', 'onGroupSubscribedList');
        $this->registerEvent('groupadded', 'onGroupAdded');
        $this->registerEvent('groupremoved', 'onGroupRemoved');
    }

    function display()
    {
        $this->view->assign(
                    'getsubscribedlist',
                    $this->call('ajaxGetGroupSubscribedList')
                    );
    }
    
    function onGroupSubscribedList($list) {
        $html = $this->prepareList($list);
        RPC::call('movim_fill', 'groupsubscribedlistconfig', $html); 
    }
    
    function prepareList($list) {
        $configlist = $this->tpl();
        $sd = new \Modl\SubscriptionDAO();

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

                $switch = $this->call(
                            'ajaxChangeSubscribed',
                            "'".$s->server."'",
                            "'".$s->node."'",
                            "this.checked",
                            "'".$name."'");

                $listhtml .= '
                    <li class="action">
                        <span class="icon bubble color '.stringToColor($s->node).'">
                            '.firstLetterCapitalize($s->node).'
                        </span>
                        <form>
                            <div class="action">
                                <div class="checkbox">
                                    <input
                                        type="checkbox"
                                        id="privacy'.$s->node.'"
                                        name="privacy'.$s->node.'"
                                        '.$checked.'
                                        onchange="'.$switch.'"/>
                                    <label for="privacy'.$s->node.'"></label>
                                </div>
                            </div>
                        </form>
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
        Notification::append(null, $this->__('public_groups.added', $node));
    }
    
    function onGroupRemoved($node) {
        Notification::append(null, $this->__('public_groups.removed', $node));
    }

    function ajaxChangeSubscribed($server, $node, $state, $name) {
        $data = array('title' => $name);
        
        if($state) {
            $r = new ListAdd;
            $r->setNode($node)
              ->setTo($server)
              ->setFrom($this->user->getLogin())
              ->setData($data)
              ->request();
        } else {
            $r = new ListRemove;
            $r->setNode($node)
              ->setTo($server)
              ->setFrom($this->user->getLogin())
              ->request();
        }
    }

    function ajaxGetGroupSubscribedList(){
        $r = new ListGet;
        $r->request();
    }

}
