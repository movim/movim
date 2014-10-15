<?php

/**
 * @package Widgets
 *
 * @file XEP.php
 * This file is part of MOVIM.
 * 
 * @brief A widget which display XEP info
 *
 * @author Timothée
 * 
 * See COPYING for licensing information.
 */

class XEP extends WidgetBase
{
    function load() {
    	$this->addcss('xep.css');
    }

    function display() {
        $num = $_GET['x'];

        if(preg_match('/[0-9]{4}/', $num)) {            
            $xmlraw = requestURL('http://xmpp.org/extensions/xep-'.$num.'.xml', 5);
            $xml = simplexml_load_string($xmlraw, null, LIBXML_NOCDATA);

            if($xml != false) {
                $this->view->assign('xml', $xml);
            }
            $this->view->assign('key', $num);
        }
    }

    function getJid($jid) {
        $cd = new \modl\ContactDAO;
        $c = $cd->get($jid);

        if(isset($c)) {
            return $c;
        } else {
            return new \modl\Contact;
        }
    }
}
