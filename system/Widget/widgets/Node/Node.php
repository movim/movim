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
    }

    function ajaxGetItems($server, $node)
    {
        $r = new moxl\GroupNodeGetItems();
        $r->setTo($server)
          ->setNode($node)
          ->request();
    }

    function build()
    {
    ?>
        <div id="node">
            <script type="text/javascript"><?php echo $this->genCallAjax('ajaxGetItems', "'".$_GET['s']."'", "'".$_GET['n']."'"); ?></script>
        </div>
    <?php
    }
}
