<?php

/**
 * @package Widgets
 *
 * @file Node.php
 * This file is part of MOVIM.
 *
 * @brief The items of a node
 *
 * @author Timothée	Jaussoin <edhelas_at_gmail_dot_com>
 *
 * @version 1.0
 * @date 20 October 2010
 *
 * Copyright (C)2010 MOVIM project
 *
 * See COPYING for licensing information.
 */

class Node extends WidgetCommon
{
    function WidgetLoad()
    {
		$this->registerEvent('groupsubscribed', 'onGroupSubscribed');
		$this->registerEvent('groupunsubscribed', 'onGroupUnsubscribed');
    }
    
    function onGroupSubscribed($params)
    {
        $html = $this->prepareGroup($params[0], $params[1]);
        
        RPC::call('movim_fill', 'node', RPC::cdata($html));
        RPC::commit();        
    }
    
    function onGroupUnubscribed($params)
    {
        $html = $this->prepareGroup($params[0], $params[1]);
        
        RPC::call('movim_fill', 'node', RPC::cdata($html));
        RPC::commit();
    }
    
    function onStream($id) {
        $html = $this->prepareGroup($id[0], $id[1]);
        
        if($html == '') 
            $html = t("Your feed cannot be loaded.");
        RPC::call('movim_fill', 'node', RPC::cdata($html));
        RPC::commit();
    }

    function ajaxGetItems($server, $node)
    {
        $r = new moxl\GroupNodeGetItems();
        $r->setTo($server)
          ->setNode($node)
          ->request();
    }
    
    function ajaxSubscribe($server, $node)
    {
        $g = new moxl\GroupSubscribe();
        $g->setTo($server)
          ->setNode($node)
          ->setFrom($this->user->getLogin())
          ->request();
    }
    
    function ajaxUnsubscribe($server, $node)
    {
        $g = new moxl\GroupUnsubscribe();
        $g->setTo($server)
          ->setNode($node)
          ->setFrom($this->user->getLogin())
          ->request();
    }
    
    function prepareGroup($serverid, $groupid) {
        if($this->searchSubscribed($serverid, $groupid))
            $button = '
                <a
                    href="#" 
                    class="button tiny icon back merged left"
                    onclick="movim_toggle_display(\'#groupunsubscribe\')">
                    '.t('Unsubscribe').'
                </a>';
        else
            $button = '
                <a 
                    href="#" 
                    class="button tiny icon next merged left"
                    onclick="movim_toggle_display(\'#groupsubscribe\')">
                    '.t('Subscribe').'
                </a>';
        
        $html = '
            <div class="breadcrumb">
                <a href="?q=server&s='.$_GET['s'].'">
                    '.$_GET['s'].'
                </a>
                <a href="?q=node&s='.$_GET['s'].'&n='.$_GET['n'].'">
                    '.$_GET['n'].'
                </a>
                <a>'.t('Posts').'</a>
            </div>
            <div class="posthead">
                '.$button.'<a
                    href="#"
                    onclick="'.$this->genCallAjax('ajaxGetItems', "'".$_GET['s']."'", "'".$_GET['n']."'").'"
                    class="button tiny icon follow merged right">
                    '.('Refresh').'
                </a>
            </div>
            
            <div class="popup" id="groupsubscribe">
                <form name="groupsubscribe">
                    <fieldset>
                        <legend>'.t('Subscribe').'</legend>
                        
                        <div id="subscribeadderror"></div>
                        <div class="element large mini">
                            <input name="title" placeholder="'.t('Title').'"/>
                        </div>
                    </fieldset>
                    <a 
                        class="button tiny icon yes black merged left"
                        onclick="
                            '.$this->genCallAjax('ajaxSubscribe', "'".$_GET['s']."'", "'".$_GET['n']."'").'
                            '.$this->genCallWidget(
                                                "Bookmark",
                                                "ajaxSubscribeAdd", 
                                                "'".$_GET['s']."'",
                                                "'".$_GET['n']."'",
                                                "movim_parse_form('groupsubscribe')").'"
                    >'.t('Subscribe').'</a><a 
                        class="button tiny icon black merged right" 
                        onclick="
                            movim_toggle_display(\'#groupsubscribe\');"
                    >'.t('Close').'</a>
                </form>
            </div>
            <div class="popup" id="groupunsubscribe">
                <form name="groupunsubscribe">
                    <fieldset>
                        <legend>'.t('Unsubscribe').'</legend>
                        <div class="element large mini">
                            <span>'.t('Are you sure ?').'</span>
                        </div>
                    </fieldset>
                    <a 
                        class="button tiny icon yes black merged left"
                        onclick="
                            '.$this->genCallAjax('ajaxUnsubscribe', "'".$_GET['s']."'", "'".$_GET['n']."'").'
                            '.$this->genCallWidget(
                                                "Bookmark",
                                                "ajaxSubscribeRemove", 
                                                "'".$_GET['s']."'",
                                                "'".$_GET['n']."'").'"
                    >'.t('Unsubscribe').'</a><a 
                        class="button tiny icon black merged right" 
                        onclick="
                            movim_toggle_display(\'#groupunsubscribe\');"
                    >'.t('Close').'</a>
                </form>
            </div>';
        
        $title = '';
        
        $pd = new modl\PostDAO();
        $posts = $pd->getGroup($serverid, $groupid);
        
        $html .= $title;

        $html .= $this->preparePosts($posts);
        
        return $html;
    }
    
    function searchSubscribed($server, $node) {
        $c = Cache::c('bookmark');
        foreach($c as $bookmark) {
            if(
                $bookmark['type'] == 'subscription' && 
                $bookmark['server'] == $server &&
                $bookmark['node'] == $node) {
                return true;
            }
        }
        return false;
    }

    function build()
    {
    ?>
        <div class="tabelem protect red" id="node" title="<?php echo t('Posts'); ?>">
            <?php echo $this->prepareGroup($_GET['s'], $_GET['n']); ?>
        </div>
    <?php
    }
}
