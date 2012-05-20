<?php

/**
 * @package Widgets
 *
 * @file Profile.php
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

class Profile extends WidgetBase
{

    private static $status;

    function WidgetLoad()
    {
        $this->addcss('profile.css');
        $this->addjs('profile.js');
        $this->registerEvent('myvcard', 'onMyVcardReceived');
    }
    
    function onMyVcardReceived($vcard = false)
    {
		$html = $this->prepareVcard($vcard);
        RPC::call('movim_fill', 'profile', RPC::cdata($html));
    }
    
	function ajaxSetStatus($status)
	{
        // We update the cache with our status and presence
        $presence = Cache::c('presence');
        Cache::c(
            'presence', 
            array(
                'status' => rawurldecode($status),
                'show' => $presence['show'],
                'boot' => false
                )
        );
		$this->xmpp->setStatus(rawurldecode($status), $presence['show']);
	}
    
    function prepareVcard($vcard = false)
    {
        global $sdb;
        $me = $sdb->select('Contact', array('key' => $this->user->getLogin(), 'jid' => $this->user->getLogin()));
        
        $presence = Cache::c('presence');
        
        if(isset($me[0])) {
            $me = $me[0];
            $html = '
				<table>
					<tr>
						<td>
							<img src="'.$me->getPhoto().'">
						</td>
						<td>
							<h1>'.$me->getTrueName().'</h1>
						</td>
					</tr>
				</table>
				';
            $html .= '
                <input 
                    type="text" 
                    id="status" 
                    value="'.$presence['status'].'"
                    onkeypress="if(event.keyCode == 13) {'.$this->genCallAjax('ajaxSetStatus', "getStatusText()").' return false;}"
                />
                <br />
                ';
        } else {
			$html .= t('No profile yet ?').'<br /><br />';
			$html .= '<a class="button icon add" style="padding-left: 25px;" href="?q=profile">'.t("Create my vCard").'</a><br /><br />';
		}
        
        return $html;
    }
    
    function build()
    {
    ?>
    
        <div id="profile">
			<?php 
				echo $this->prepareVcard();
			?>
        </div>
    <?php
    }
}
