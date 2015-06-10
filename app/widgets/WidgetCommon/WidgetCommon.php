<?php

/**
 * @file WidgetCommon.php
 * This file is part of MOVIM.
 *
 * @brief The widgets commons methods.
 *
 * @author Timothée Jaussoin <edhelas@gmail.com>
 *
 * @date 08 march 2012
 *
 * Copyright (C)2010 MOVIM Project
 *
 * See COPYING for licensing information.
 *
 */

class WidgetCommon extends WidgetBase {

    function ajaxShowPosition($pos)
    {
        list($lat,$lon) = explode(',', $pos);    

        $pos = json_decode(
                    file_get_contents('http://nominatim.openstreetmap.org/reverse?format=json&lat='.$lat.'&lon='.$lon.'&zoom=27&addressdetails=1')
                );

        RPC::call('movim_fill', 'postpublishlocation' , (string)$pos->display_name);
        RPC::commit();
    }

    function ajaxPublishItem($server, $node, $form)
    {
        $content = $form['content'];
        $title   = $form['title'];

        $geo = false;

        if(isset($form['latlonpos']) && $form['latlonpos'] != '') {
            list($lat,$lon) = explode(',', $form['latlonpos']);
            
            $pos = json_decode(
                        file_get_contents('http://nominatim.openstreetmap.org/reverse?format=json&lat='.$lat.'&lon='.$lon.'&zoom=27&addressdetails=1')
                    );
                    
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
        }

        if($content != '') {
            $content = Markdown::defaultTransform($content);
            
            $p = new PostPublish;
            $p->setFrom($this->user->getLogin())
              ->setTo($server)
              ->setNode($node)
              ->setLocation($geo)
              ->setTitle($title)
              ->setContentHtml(rawurldecode($content))
              ->enableComments()
              ->request();
        }
        
    }
}
