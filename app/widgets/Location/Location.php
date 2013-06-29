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

class Location extends WidgetBase
{
    function WidgetLoad()
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

            $p = new moxl\LocationPublish();
            $p->setTo($this->user->getLogin())
              ->setGeo($geo)
              ->request();
        } else {
            Notification::appendNotification(t('Wrong position'), 'error');
        }
    }
    
    function onLocationPublished($me)
    {
        $html = $me->getPlace();
        RPC::call('movim_fill', 'mapdata', $html);
        
        Notification::appendNotification(t('Location updated'), 'success');
        RPC::call('movim_delete', 'mapdiv');
        RPC::commit();
    }
    
    function onLocationPublishError($error)
    {
        Notification::appendNotification($error, 'error');

        RPC::call('movim_delete', 'mapdiv');
        RPC::call('movim_delete', 'mapdata');
        RPC::commit();
    }
    
    function prepareProfileData()
    {
        $submit = $this->genCallAjax('ajaxLocationPublish', "getMyPositionData()");
        
        $cd = new modl\ContactDAO();
        $c = $cd->get($this->user->getLogin());

        if($c->loctimestamp) {
            $data = prepareDate(strtotime($c->loctimestamp)).'<br /><br />';
            $data .= $c->getPlace();
        } else {
            $data = '';
        }
        
        $html = '';
        
        $html .= '
            <h2>'.t('Location').'</h2>
            <div id="location">
                <div id="mapdata" style="margin-bottom: 10px;">'.$data.'</div>
                <div id="mapdiv" style="width: auto; height: 250px; display: none;"></div>
                <div class="clear"></div>
                <a 
                    class="button color green icon geo" 
                    style="margin-top: 1em; display: block;"
                    onclick="getMyPosition(); this.style.display = \'none\';">'.
                    t('Update my position').'
                </a>
                <a 
                    id="mypossubmit" 
                    style="display: none;" 
                    class="button color green icon yes" 
                    onclick="'.$submit.' hidePositionChoice();">'.t('Accept').'</a><a 
                    style="display: none; margin-top: 1em;" 
                    id="myposrefuse" 
                    onclick="hidePositionChoice();"
                    class="button tiny icon alone no merged right"></a>
            </div>';
        
        return $html;
    }
    
    function build()
    {
        echo $this->prepareProfileData();
    }
}
