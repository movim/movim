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

class ProfileData extends WidgetBase
{
    function WidgetLoad()
    {
        $this->addjs('profiledata.js');
    }
    
    function ajaxLocationPublish($pos)
    {
        movim_log($pos = json_decode($pos));
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
            movim_log($geo);
            
            $p = new moxl\LocationPublish();
            $p->setTo($this->user->getLogin())
              ->setGeo($geo)
              ->request();
        } else {
            $html = '
                <div class="message error">'.t('Wrong position').'</div>';
            RPC::call('movim_fill', 'maperror', RPC::cdata($html));
            RPC::commit();
        }
    }
    
    function prepareProfileData()
    {
        $submit = $this->genCallAjax('ajaxLocationPublish', "getMyPositionData()");
        
        $html = '';
        
        $html .= '
            <h2>'.t('Location').'</h2>
            <div id="maperror"></div>
            <div id="mapdata"></div>
            <div id="mapdiv" style="width: auto; height: 250px; display: none;"></div><br />
            <a class="button tiny icon add" onclick="getMyPosition(); this.style.display = \'none\';">'.t('Get my position').'</a>
            <a id="mypossubmit" style="display: none;" class="button tiny icon yes merged left" onclick="'.$submit.'">'.t('Accept').'</a><a style="display: none;" id="myposrefuse" class="button tiny icon yes merged right">'.t('Cancel').'</a>';
        return $html;
    }
    
    function build()
    {
        echo $this->prepareProfileData();
    }
}
