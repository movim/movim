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

use Moxl\Xec\Action\Location\Publish;

class Location extends WidgetBase
{
    function load()
    {
        $this->addjs('location.js');
		$this->registerEvent('locationpublished', 'onLocationPublished');
		$this->registerEvent('locationpublisherror', 'onLocationPublishError');
    }
    
    function ajaxLocationPublish($pos)
    {
        $pos = json_decode($pos);
        if($pos->place_id) {
            $geo = array(
                'latitude'      => (string)$pos->lat,
                'longitude'     => (string)$pos->lon,
                'altitude'      => (string)$pos->alt,
                'country'       => (string)$pos->address->country,
                'countrycode'   => (string)$pos->address->country_code,
                'region'        => (string)$pos->address->county,
                'postalcode'    => (string)$pos->address->postcode,
                'locality'      => (string)$pos->address->city,
                'street'        => (string)$pos->address->path,
                'building'      => (string)$pos->address->building,
                'text'          => (string)$pos->display_name,
                'uri'           => ''//'http://www.openstreetmap.org/'.urlencode('?lat='.(string)$pos->lat.'&lon='.(string)$pos->lon.'&zoom=10')
                );

            $p = new Publish;
            $p->setTo($this->user->getLogin())
              ->setGeo($geo)
              ->request();
        } else {
            Notification::append(null, $this->__('location.wrong_postition'));
        }
    }
    
    function onLocationPublished($me)
    {
        $html = $me->getPlace();
        RPC::call('movim_fill', 'mapdata', $html);
        
        Notification::append(null, $this->__('location.updated'));
        RPC::commit();
    }
    
    function onLocationPublishError($error)
    {
        Notification::append(null, $error);

        RPC::call('movim_delete', 'mapdiv');
        RPC::call('movim_delete', 'mapdata');
        RPC::commit();
    }
    
    function prepareProfileData()
    {
        $submit = $this->call('ajaxLocationPublish', "getMyPositionData()");
        
        $cd = new \Modl\ContactDAO();
        $c = $cd->get($this->user->getLogin());

        if($c->loctimestamp) {
            $data = prepareDate(strtotime($c->loctimestamp)).'<br /><br />';
            $data .= $c->getPlace();
        } else {
            $data = '';
        }
        
        $html = '';
        
        $html .= '
            <div id="location">
                <div id="mapdata" style="margin: 1em 0;">'.$data.'</div>
                <div id="mapdiv" style="width: auto; height: 250px; display: none;"></div>
                <div class="clear"></div>
                <a 
                    class="button color green" 
                    style="margin-top: 1em;"
                    onclick="getMyPosition(); this.style.display = \'none\';">
                    <i class="fa fa-compass"></i> '.$this->__('location.update').'
                </a>
                <a 
                    id="mypossubmit" 
                    style="display: none; margin-top: 1em; float: right;"
                    class="button color green merged left" 
                    onclick="'.$submit.' hidePositionChoice();">
                    <i class="fa fa-check"></i> '.$this->__('button.accept').'</a>
            </div>';
        
        return $html;
    }
}
