<?php

/**
 * @package Widgets
 *
 * @file ContactInfo.php
 * This file is part of MOVIM.
 *
 * @brief Display some informations on a Contact
 *
 * @author Jaussoin Timothée <edhelas@gmail.com>
 *
 * Copyright (C)2013 MOVIM project
 *
 * See COPYING for licensing information.
 */

class ContactInfo extends WidgetCommon
{    
    function load()
    {
    	$this->addcss('contactinfo.css');
        $this->registerEvent('tune', 'onTune');    
    }

    function onTune($from)
    {
        $html = $this->prepareContactInfo($from);     
        RPC::call('movim_fill', 'contactinfo', $html);
    }

    function prepareContactInfo($from = false)
    {
        $cd = new \Modl\ContactDAO();
        if($from != $this->user->getLogin())
            $c = $cd->getRosterItem($from);
        else
            $c = $cd->get($from);
        
        $html = '';
        
        if(isset($c)) {
            // Mood
            if($c->mood) {
                $moodarray = getMood();
                
                $html .= '<h2>'.$this->__('mood.title').'</h2>';
                $mood = '';
                foreach(unserialize($c->mood) as $m)
                    $mood .= $moodarray[$m].',';
                $html .= $this->__('mood.im').substr($mood, 0, -1).'<br />';
            }
            
            // Tune
            if($c->tuneartist || $c->tunetitle) {
                $album = $artist = $title = $img = '';
                
                $html .= '<h2>'.$this->__('listen.title').'</h2>';
                if($c->tuneartist)
                    $artist = $c->tuneartist. ' - ';
                if($c->tunetitle)
                    $title = $c->tunetitle;
                if($c->tunesource)
                    $album = $this->__('listen.on').' '.$c->tunesource;
                    
                if($c->tunesource) {
                    $l = str_replace(
                        ' ', 
                        '%20', 
                        'http://ws.audioscrobbler.com/2.0/?method=album.getinfo&api_key=80c1aa3abfa9e3d06f404a2e781e38f9&artist='.
                            $c->tuneartist.
                            '&album='.
                            $c->tunesource.
                            '&format=json'
                        );
                    
                    $json = json_decode(requestURL($l, 2));
                    
                    $img = $json->album->image[2]->{'#text'};
                    $url = $json->album->url;
                    if(isset($img) && $img != '') {
                        $img = '
                            <a href="'.$url.'" target="_blank">
                                <img src="'.$img.'"/>
                            </a>';
                    }
                }
                    
                $html .= $artist.$title.' '.$album.$img;
            }
            
            // Last seen
            if($c->delay != null 
                && $c->delay 
                && $c->delay != '0000-00-00 00:00:00') {
                $html .= '<h2>'.$this->__('last.title').'</h2>';
                $html .= prepareDate(strtotime($c->delay)).'<br />';
            }

            // Client informations
            if( $c->node != null 
                && $c->ver != null 
                && $c->node 
                && $c->ver) {                
                $node = $c->node.'#'.$c->ver;

                $cad = new \Modl\CapsDAO();
                $caps = $cad->get($node);

                $clienttype = getClientTypes();
                        
                if(isset($caps) && $caps->name != '' && $caps->type != '' ) {
                    $cinfos = '';
                    if(isset($clienttype[$caps->type]))
                        $type = ' ('.$clienttype[$caps->type].')';
                    else
                        $type = '';
                    
                    $cinfos .=  $caps->name.$type.'<br />';
                    
                    $html .='<h2>'.$this->__('client.title').'</h2>' . $cinfos;
                }
            }

            $html .= '<div class="clear"></div>';

            // Accounts
            if($c->twitter && $c->twitter != '') {
                $html .= '
                    <a
                        class="button color blue"
                        target="_blank"
                        href="https://twitter.com/'.$c->twitter.'">
                        <i class="fa fa-twitter"></i> @'.$c->twitter.'
                    </a>';
            }
            
            if($c->skype && $c->skype != '') {
                $html .= '
                    <a
                        class="button color green"
                        target="_blank"
                        href="callto://'.$c->skype.'">
                        <i class="fa fa-skype"></i> '.$c->skype.'
                    </a>';
            }
            
            if($c->yahoo && $c->yahoo != '') {
                $html .= '
                    <a
                        class="button color purple"
                        target="_blank"
                        href="ymsgr:sendIM?'.$c->yahoo.'">
                        <i class="fa fa-yahoo"></i> '.$c->yahoo.'
                    </a>';
            }

            $html .= '<div class="clear"></div>';
        }
        
        return $html;
    }
}
