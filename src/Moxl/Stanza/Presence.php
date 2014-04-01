<?php

namespace Moxl\Stanza;

/*
 * The presence builder
 */
function presenceMaker($to = false, $status = false, $show = false, $priority = 0, $type = false)
{
    $session = \Sessionx::start();
    
    $toxml = $typexml = $statusxml = $showxml = $priorityxml = '';

    if($to != false)
        $toxml = 'to="'.str_replace(' ', '\40', $to).'"';
        
    if($type != false)
        $typexml = 'type="'.$type.'"';
        
    if($status != false)
        $statusxml = '<status>'.$status.'</status>';
        
    if($show != false)
        $showxml = '<show>'.$show.'</show>';
        
    if($priority != 0)
        $priorityxml = '<priority>'.$priority.'</priority>';
        
    return '
        <presence
            '.$toxml.'
            xmlns="jabber:client"
            from="'.$session->user.'@'.$session->host.'/'.$session->ressource.'" '.$typexml.'
            id="'.$session->id.'">
            '.$statusxml.'
            '.$showxml.'
            '.$priorityxml.'
            <c xmlns="http://jabber.org/protocol/caps"
            hash="sha-1"
            ext="pmuc-v1 share-v1 voice-v1 video-v1 camera-v1"
            node="http://moxl.movim.eu/"
            ver="'.\Moxl\Utils::generateCaps().'" />
        </presence>';
}

/*
 * Simple presence without parameters
 */
function presenceSimple()
{
    $xml = presenceMaker(false, false, false, false, false);
    \Moxl\request($xml);
}

/*
 * Subscribe to someone presence
 */
function presenceUnavaiable($to = false, $status = false, $type = false)
{
    $xml = presenceMaker($to, $status, false, false, 'unavailable');
    \Moxl\request($xml, $type);
}

/*
 * Subscribe to someone presence
 */
function presenceSubscribe($to, $status)
{
    $xml = presenceMaker($to, $status, false, false, 'subscribe');
    \Moxl\request($xml);
}

/*
 * Unsubscribe to someone presence
 */
function presenceUnsubscribe($to, $status)
{
    $xml =  presenceMaker($to, $status, false, false, 'unsubscribe');
    \Moxl\request($xml);
}

/*
 * Accept someone presence \Moxl\request
 */
function presenceSubscribed($to)
{
    $xml = presenceMaker($to, false, false, false, 'subscribed');
    \Moxl\request($xml);
}

/*
 * Refuse someone presence \Moxl\request
 */
function presenceUnsubscribed($to)
{
    $xml =  presenceMaker($to, false, false, false, 'unsubscribed');
    \Moxl\request($xml);
}

/*
 * Enter a chat room
 */
function presenceMuc($to, $nickname = false)
{
    $session = \Sessionx::start();
    
    if($nickname == false)
        $nickname = $session->user;
    
    $xml = '
        <presence
            from="'.$session->user.'@'.$session->host.'/'.$session->ressource.'" 
            id="'.$session->id.'"
            to="'.$to.'/'.$nickname.'">
            <x xmlns="http://jabber.org/protocol/muc"/>
        </presence>';

    \Moxl\request($xml);
}

/*
 * Go away
 */
function presenceAway($status)
{
    $xml =  presenceMaker(false, $status, 'away', false, false);
    \Moxl\request($xml);
}

/*
 * Go chatting
 */
function presenceChat($status)
{
    $xml =  presenceMaker(false, $status, 'chat', false, false);
    \Moxl\request($xml);
}

/*
 * Do not disturb
 */
function presenceDND($status)
{
    $xml = presenceMaker(false, $status, 'dnd', false, false);
    \Moxl\request($xml);
}

/*
 * eXtended Away
 */
function presenceXA($status)
{
    $xml =  presenceMaker(false, $status, 'xa', false, false);
    \Moxl\request($xml);
}
