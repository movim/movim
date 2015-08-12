<?php

namespace Moxl\Stanza;

class Presence {
    /*
     * The presence builder
     */
    static function maker($to = false, $status = false, $show = false, $priority = 0, $type = false)
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
                from="'.$session->user.'@'.$session->host.'/'.$session->resource.'" '.$typexml.'
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
    static function simple()
    {
        $xml = self::maker(false, false, false, false, false);
        \Moxl\API::request($xml);
    }

    /*
     * Subscribe to someone presence
     */
    static function unavailable($to = false, $status = false, $type = false)
    {
        $xml = self::maker($to, $status, false, false, 'unavailable');
        \Moxl\API::request($xml, $type);
    }

    /*
     * Subscribe to someone presence
     */
    static function subscribe($to, $status)
    {
        $xml = self::maker($to, $status, false, false, 'subscribe');
        \Moxl\API::request($xml);
    }

    /*
     * Unsubscribe to someone presence
     */
    static function unsubscribe($to, $status)
    {
        $xml = self::maker($to, $status, false, false, 'unsubscribe');
        \Moxl\API::request($xml);
    }

    /*
     * Accept someone presence \Moxl\API::request
     */
    static function subscribed($to)
    {
        $xml = self::maker($to, false, false, false, 'subscribed');
        \Moxl\API::request($xml);
    }

    /*
     * Refuse someone presence \Moxl\API::request
     */
    static function unsubscribed($to)
    {
        $xml = self::maker($to, false, false, false, 'unsubscribed');
        \Moxl\API::request($xml);
    }

    /*
     * Enter a chat room
     */
    static function muc($to, $nickname = false)
    {
        $session = \Sessionx::start();
        
        if($nickname == false)
            $nickname = $session->user;
        
        $xml = '
            <presence
                from="'.$session->user.'@'.$session->host.'/'.$session->resource.'" 
                id="'.$session->id.'"
                to="'.$to.'/'.$nickname.'">
                <x xmlns="http://jabber.org/protocol/muc"/>
            </presence>';

        \Moxl\API::request($xml);
    }

    /*
     * Go away
     */
    static function away($status)
    {
        $xml = self::maker(false, $status, 'away', false, false);
        \Moxl\API::request($xml);
    }

    /*
     * Go chatting
     */
    static function chat($status)
    {
        $xml = self::maker(false, $status, 'chat', false, false);
        \Moxl\API::request($xml);
    }

    /*
     * Do not disturb
     */
    static function DND($status)
    {
        $xml = self::maker(false, $status, 'dnd', false, false);
        \Moxl\API::request($xml);
    }

    /*
     * eXtended Away
     */
    static function XA($status)
    {
        $xml = self::maker(false, $status, 'xa', false, false);
        \Moxl\API::request($xml);
    }
}
