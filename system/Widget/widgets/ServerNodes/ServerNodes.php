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
        $this->registerEvent('disconodes', 'onDiscoNodes');
        $this->registerEvent('creationsuccess', 'onCreationSuccess');
        $this->registerEvent('creationerror', 'onCreationError');
    }

    function onDiscoNodes($items)
    {
        $html = '<ul class="list">';

        foreach($items[0] as $item) {
            
            if (substr($item->attributes()->node, 0, 20) != 'urn:xmpp:microblog:0') {
                $name = '';
                if(isset($item->attributes()->name))
                    $name = $item->attributes()->name;
                else
                    $name = $item->attributes()->node;
            
                $html .= '
                    <li>
                        <a href="?q=node&s='.$item->attributes()->jid.'&n='.$item->attributes()->node.'">'.
                            $name.'
                        </a>
                    </li>';
            }
        }

        $html .= '</ul>';
        
        $submit = $this->genCallAjax('ajaxCreateGroup', "movim_parse_form('groupCreation')");
        
        $html .= '<div class="popup" id="groupCreation">
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
        RPC::commit();
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
        if (substr($_GET['s'], 0, 7) == 'pubsub.') {
            $create = '
            <a 
                class="button tiny icon add" 
                onclick="movim_toggle_display(\'#groupCreation\')">
                '.t("Create a new group").'
            </a>';
        }
        
    ?>
    <div class="breadcrumb protect red ">
        <a href="?q=server&s=<?php echo $_GET['s']; ?>">
            <?php echo $_GET['s']; ?>
        </a>
        <a><?php echo t('Topics'); ?></a>
    </div> 
    <div class="posthead ">
        <a
            href="#"
            onclick="<?php echo $this->genCallAjax('ajaxGetNodes', "'".$_GET['s']."'"); ?>; this.style.display = 'none';"
            class="button tiny icon follow">
            <?php echo t('Refresh'); ?>
        </a>
        <?php echo $create; ?>
    </div>
    <div id="servernodes" class="tabelem paddedtop" title="<?php echo t('Server'); ?>">
        <div id="newGroupForm"></div>
        <div id="servernodeslist" title="<?php echo t('Groups');?>">
            <script type="text/javascript"><?php echo $this->genCallAjax('ajaxGetNodes', "'".$_GET['s']."'"); ?></script>
        </div>
    </div>
    <?php
    }
}
