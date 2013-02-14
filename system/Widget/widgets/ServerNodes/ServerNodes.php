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
    }

    function onDiscoNodes($items)
    {
        $html = '<ul>';

        foreach($items as $item) {
            $html .= '
                <li>
                    <a href="?q=node&s='.$item->attributes()->jid.'&n='.$item->attributes()->node.'">'.
                        $item->attributes()->node. ' - '.
                        $item->attributes()->name.'
                    </a>
                </li>';
        }

        $html .= '</ul>';

        RPC::call('movim_fill', 'servernodes', RPC::cdata($html));
        RPC::commit();
    }
    
    function onDiscoItems($items)
    {
        $html = '<ul>';

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

        RPC::call('movim_fill', 'servernodes', RPC::cdata($html));
        RPC::commit();
    }

    function ajaxGetNodes($server)
    {
        $r = new moxl\GroupServerGetNodes();
        $r->setTo($server)->request();
    }

    function build()
    {
    ?><br /><br /><br /><br />
        <div id="servernodes">
            <script type="text/javascript"><?php echo $this->genCallAjax('ajaxGetNodes', "'".$_GET['s']."'"); ?></script>
        </div>
    <?php
    }
}
