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
    function WidgetLoad()
    {
        $this->registerEvent('tune', 'onTune');    
    }
    
    function onTune($from)
    {
        $html = $this->prepareContactInfo($from);     
        RPC::call('movim_fill', 'contactinfo', $html);
    }

    function prepareContactInfo($from = false)
    {
        $cd = new \modl\ContactDAO();
        if($from != $this->user->getLogin())
            $c = $cd->getRosterItem($from);
        else
            $c = $cd->get($from);
        
        $html = '';
        
        if(isset($c)) {
            // Mood
            if($c->mood) {
                $moodarray = getMood();
                
                $html .= '<h2>'.t('Mood').'</h2>';
                $mood = '';
                foreach(unserialize($c->mood) as $m)
                    $mood .= $moodarray[$m].',';
                $html .= t("I'm ").substr($mood, 0, -1).'<br />';
            }
            
            // Tune
            if($c->tuneartist || $c->tunetitle) {
                $album = $artist = $title = $img = '';
                
                $html .= '<h2>'.t('Listening').'</h2>';
                if($c->tuneartist)
                    $artist = $c->tuneartist. ' - ';
                if($c->tunetitle)
                    $title = $c->tunetitle;
                if($c->tunesource)
                    $album = t('on').' '.$c->tunesource;
                    
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
                    
                    $json = json_decode(file_get_contents($l));
                    
                    $img = $json->album->image[2]->{'#text'};
                    $url = $json->album->url;
                    if(isset($img)) {
                        $img = '
                            <br />
                            <br />
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
                $html .= '<h2>'.t('Last seen').'</h2>';
                $html .= prepareDate(strtotime($c->delay)).'<br />';
            }

            // Client informations
            if( $c->node != null 
                && $c->ver != null 
                && $c->node 
                && $c->ver) {                
                $node = $c->node.'#'.$c->ver;

                $cad = new \modl\CapsDAO();
                $caps = $cad->get($node);

                $clienttype = 
                    array(
                        'bot' => t('Bot'),
                        'pc' => t('Desktop'),
                        'phone' => t('Phone'),
                        'handheld' => t('Phone'),
                        'web' => t('Web'),
                        'registered' => t('Registered')
                        );
                        
                if(isset($caps) && $caps->name != '' && $caps->type != '' ) {
                    $cinfos = '';
                    $cinfos .=  $caps->name.' ('.$clienttype[$caps->type].')<br />';
                    
                    $html .='<h2>'.t('Client Informations').'</h2>' . $cinfos;
                }
            }
        }
        
        return $html;
    }
    
    function build() {
        ?>
        <div id="contactinfo">
            <?php echo $this->prepareContactInfo($_GET['f']); ?>
        </div>  
        <?php
    }
}
