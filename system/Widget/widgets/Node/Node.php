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
		$this->registerEvent('stream', 'onStream');
		$this->registerEvent('pubsubsubscribed', 'onPubsubSubscribed');
		$this->registerEvent('pubsubunsubscribed', 'onPubsubUnsubscribed');
    }
    
    function onPubsubSubscribed($params)
    {        
        $html = $this->prepareGroup($params[0], $params[1]);
        RPC::call('movim_fill', 'node', $html);    
    }
    
    function onPubsubUnsubscribed($params)
    {
        $html = $this->prepareGroup($params[0], $params[1]);
        RPC::call('movim_fill', 'node', $html);
    }
    
    function onStream($payload) {
        $html = $this->prepareGroup($payload['from'], $payload['node']);

        if($html == '') 
            $html = t("Your feed cannot be loaded.");
        RPC::call('movim_fill', md5($payload['from'].$payload['node']), $html);
    }

    function ajaxGetItems($server, $node)
    {
        $r = new moxl\PubsubGetItems();
        $r->setTo($server)
          ->setNode($node)
          ->request();
    }
    
    function ajaxSubscribe($data, $server, $node)
    {
        $g = new moxl\PusubSubscribe();
        $g->setTo($server)
          ->setNode($node)
          ->setFrom($this->user->getLogin())
          ->setData($data)
          ->request();
    }
    
    function ajaxUnsubscribe($server, $node)
    {
        $sd = new \modl\SubscriptionDAO();

        foreach($sd->get($server, $node) as $s) {
            $g = new moxl\PubsubUnsubscribe();
            $g->setTo($server)
              ->setNode($node)
              ->setSubid($s->subid)
              ->setFrom($this->user->getLogin())
              ->request();
        }
    }
    
    function ajaxGetSubscriptions($server, $node)
    {
        $r = new moxl\PubsubGetSubscriptions();
        $r->setTo($server)
          ->setNode($node)
          ->request();
    }
    
    function prepareGroup($serverid, $groupid) {
        if($this->searchSubscription($serverid, $groupid))
            $button = '
                <a
                    href="#" 
                    class="button tiny icon back"
                    onclick="movim_toggle_display(\'#groupunsubscribe\')">
                    '.t('Unsubscribe').'
                </a>';
        else
            $button = '
                <a 
                    href="#" 
                    class="button tiny icon next"
                    onclick="movim_toggle_display(\'#groupsubscribe\')">
                    '.t('Subscribe').'
                </a>';
        
        $html = '
            <div class="breadcrumb">
                <a href="?q=server&s='.$serverid.'">
                    '.$serverid.'
                </a>
                <a href="?q=node&s='.$serverid.'&n='.$groupid.'">
                    '.$groupid.'
                </a>
                <a>'.t('Posts').'</a>
            </div>
            <div class="posthead">
                '.$button.'
                <a
                    href="#"
                    onclick="'.$this->genCallAjax('ajaxGetItems', "'".$serverid."'", "'".$groupid."'").' this.style.display = \'none\'"
                    class="button tiny icon follow">
                    '.('Refresh').'
                </a>
                
                <a 
                    class="button tiny icon yes"
                    onclick="
                        '.$this->genCallAjax('ajaxGetSubscriptions', "'".$serverid."'", "'".$groupid."'").'"
                >'.t('Get Subscription').'</a>
            </div>
            
            <div class="popup" id="groupsubscribe">
                <form name="groupsubscribe">
                    <fieldset>
                        <legend>'.t('Subscribe').'</legend>
                        <div class="element large mini">
                            <input type="checkbox" name="listgroup" id="listgroup"/>
                            <span><label for="listgroup">'.t('Make your membership to this group public to your friends').'</label></span>
                        </div>
                        <div class="element large mini">
                            <input type="text" name="title" value="'.$groupid.'" id="grouptitle"/>
                            <span><label for="grouptitle">'.t('Give a nickname to this group if you want').'</label></span>
                        </div>
                    </fieldset>
                    <a 
                        class="button tiny icon yes black merged left"
                        onclick="
                            '.$this->genCallAjax('ajaxSubscribe', "movim_parse_form('groupsubscribe')", "'".$serverid."'", "'".$groupid."'").'"
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
                            '.$this->genCallAjax('ajaxUnsubscribe', "'".$serverid."'", "'".$groupid."'").'"
                    >'.t('Unsubscribe').'</a><a 
                        class="button tiny icon black merged right" 
                        onclick="
                            movim_toggle_display(\'#groupunsubscribe\');"
                    >'.t('Close').'</a>
                </form>
            </div>';
        
        $title = '';
        
        $pd = new modl\PostnDAO();
        $posts = $pd->getNode($serverid, $groupid, 0, 10);
        
        $html .= $title;
        
        if($this->searchSubscription($serverid, $groupid))
            $html .= $this->prepareSubmitForm($serverid, $groupid);

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
    
    function searchSubscription($server, $node) {
        $sd = new \modl\SubscriptionDAO();
        
        foreach($sd->get($server, $node) as $s) {
            if($s->subscription == 'subscribed')
                return true;
        }
        return false;
    }

    function build()
    {
    ?>
        <div class="tabelem protect red" id="node" title="<?php echo t('Posts'); ?>">
            <div id="<?php echo md5($_GET['s'].$_GET['n']); ?>">
                <?php echo $this->prepareGroup($_GET['s'], $_GET['n']); ?>
            </div>
        </div>
    <?php
    }
}
