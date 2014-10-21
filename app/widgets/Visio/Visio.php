<?php

/**
 * @package Widgets
 *
 * @file Visio.php
 * This file is part of Movim.
 * 
 * @brief A jabber chat widget.
 *
 * @author Timothée Jaussoin
 * 
 * See COPYING for licensing information.
 */
 
//require_once(APP_PATH . "widgets/ChatExt/ChatExt.php");

class Visio extends WidgetBase
{
	function load()
	{
    	$this->addcss('visio.css');
    	$this->addjs('visio.js');
    	$this->addjs('adapter.js');
    	$this->addjs('webrtc.js');
    	$this->addjs('turn.js');

        //$s = Session::start('movim');
        //var_dump($s->get('jingleSid'));

        if(isset($_GET['f'])) {
            list($jid, $ressource) = explode('/', htmlentities($_GET['f']));

            $json = requestURL('https://computeengineondemand.appspot.com/turn?username=93773443&key=4080218913', 1);
            $this->view->assign('turn_list'   , $json);
            
            $cd = new \Modl\ContactDAO();
            $contact = $cd->get($jid);

            if(!$contact)
                $contact = new modl\Contact();

            $this->view->assign('avatar',$contact->getPhoto('l'));
            $this->view->assign('name'  ,$contact->getTrueName());
            $this->view->assign('jid'   ,$jid);
            $this->view->assign('ressource'   ,$ressource);
        } 
    }
}
