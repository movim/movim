<?php

/**
 * @package Widgets
 *
 * @file ServerNodes.php
 * This file is part of MOVIM.
 *
 * @brief The Profile widget
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

class ServerNodes extends WidgetCommon
{
    function WidgetLoad()
    {
		$this->registerEvent('discoitems', 'onDiscoItems');
		$this->registerEvent('discoitemserror', 'onDiscoItemsError');
        $this->registerEvent('disconodes', 'onDiscoNodes');
        $this->registerEvent('creationsuccess', 'onCreationSuccess');
        $this->registerEvent('creationerror', 'onCreationError');
    }
    
    function onDiscoItemsError($error)
    {
        Notification::appendNotification($error, 'error');
        RPC::call('movim_fill', 'servernodeshead', '');
    }

    function onDiscoNodes($items)
    {

        
        $submit = $this->genCallAjax('ajaxCreateGroup', "movim_parse_form('groupCreation')");
        
        $head = '
            <a 
                class="button tiny icon add" 
                onclick="movim_toggle_display(\'#groupCreation\')">
                '.t("Create a new group").'
            </a>';
          
        if(reset($items) != false) 
            $html .= $this->prepareServer($items[1]);

        $html .= '
            <div class="popup" id="groupCreation">
                <form name="groupCreation">
                    <fieldset>
                        <legend>'.t('Give a friendly name to your group').'</legend>
                        <div class="element large mini">
                            <input name="title" placeholder="'.t('My Little Pony - Fan Club').'"/>
                        </div>
                        <input type="hidden" name="server" value="'.$items[1].'"/>
                    </fieldset>
                    <a 
                        class="button tiny icon yes black merged left"
                        onclick="'.$submit.'"
                    >'.
                            t('Add').'
                    </a><a 
                        class="button tiny icon black merged right" 
                        onclick="movim_toggle_display(\'#groupCreation\')"
                    >'.
                            t('Close').'
                    </a>
                </form>
            </div>';

        RPC::call('movim_fill', 'servernodeslist', $html);
        RPC::call('movim_fill', 'servernodeshead', $head);
        RPC::commit();
    }
    
    function onDiscoItems($items)
    {
        $html = '<ul class="list">';

        foreach($items as $item) {
            $html .= '
                <li>
                    <a href="?q=server&s='.$item->attributes()->jid.'">'.
                        $item->attributes()->jid. ' - '.
                        $item->attributes()->name.'
                    </a>
                </li>';
        }

        $html .= '</ul>';

        RPC::call('movim_fill', 'servernodeslist', $html);
        RPC::call('movim_fill', 'servernodeshead', '');
        RPC::commit();
    }
    
    function prepareServer($server) {
        $nd = new \modl\NodeDAO();
        
        $nodes = $nd->getNodes($server);
        
        $html = '<ul class="list">';

        foreach($nodes as $n) {
            
            if (substr($n->nodeid, 0, 20) != 'urn:xmpp:microblog:0') {
                $name = '';
                if(isset($n->title) && $n->title != '')
                    $name = $n->title;
                else
                    $name = $n->nodeid;
            
                $html .= '
                    <li>
                        <a href="?q=node&s='.$n->serverid.'&n='.$n->nodeid.'">'.
                            $name.'
                            <span class="tag">'.$n->number.'</span>
                        </a>
                    </li>';
            }
        }

        $html .= '</ul>';
        
        return $html;
    }
    
    function onCreationSuccess($items)
    {        
        $html = '<a class="" href="?q=node&s='.
            $items[0].'&n='.
            $items[1].'">'.$items[2].'</a>';

        RPC::call('movim_fill', 'servernodes', $html);
        RPC::commit();
    }
    
    function onCreationError($error) {
        RPC::call('movim_fill', 'servernodes', '');
        Notification::appendNotification(t('Error').' : '.$error, 'error');
        RPC::commit();
    }

    function ajaxGetNodes($server)
    {
        $r = new moxl\PubsubDiscoItems();
        $r->setTo($server)->request();
    }
    
    function ajaxCreateGroup($data)
    {
        //make a uri of the title
        $uri = stringToUri($data['title']);
        
        $r = new moxl\GroupCreate();
        $r->setTo($data['server'])->setNode($uri)->setData($data['title'])
          ->request();
    }

    function build()
    {
        if (substr($_GET['s'], 0, 7) == 'pubsub.')
            $server =  $this->prepareServer($_GET['s']);
        
    ?>
    <div class="breadcrumb protect red ">
        <a href="?q=server&s=<?php echo $_GET['s']; ?>">
            <?php echo $_GET['s']; ?>
        </a>
        <a><?php echo t('Topics'); ?></a>
    </div> 
    <div class="posthead " id="servernodeshead">
        <a
            href="#"
            onclick="<?php echo $this->genCallAjax('ajaxGetNodes', "'".$_GET['s']."'"); ?>; 
                this.className='button tiny icon loading'; this.onclick=null;"
            class="button tiny icon refresh">
            <?php echo t('Refresh'); ?>
        </a>
        <?php echo $create; ?>
    </div>
    <div id="servernodes" class="tabelem paddedtop" title="<?php echo t('Server'); ?>">
        <div id="newGroupForm"></div>
        <div id="servernodeslist">
            <?php echo $server; ?>
        </div>
    </div>
    <?php
    }
}
