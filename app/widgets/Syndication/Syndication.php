<?php

/**
 * @package Widgets
 *
 * @file Syndication.php
 * This file is part of MOVIM.
 *
 * @brief Create a RSS feed from user posts
 *
 * @author Jaussoin Timothée <edhelas@gmail.com>
 *
 * @version 1.0
 *
 * Copyright (C)2010 MOVIM project
 *
 * See COPYING for licensing information.
 */

class Syndication extends WidgetBase
{
    function build()
    {
        $from = $_GET['f'];
        
        if($_GET['n'])
            $node = $_GET['n'];
        else
            $_GET['n'] = false;
        
        if(isset($from)) {          
            $pd = new \modl\PostnDAO();
            $messages = $pd->getPublic($from, $node);
        

            if(!empty($messages)) {
                header("Content-Type: application/atom+xml; charset=UTF-8");
                
                $xml = '
                    <?xml version="1.0" encoding="utf-8"?>
                    <feed xmlns="http://www.w3.org/2005/Atom">
                        <title>'.t("%s's feed",$messages[0]->getContact()->getTrueName()).'</title>
                        <updated>'.date('c').'</updated>
                        <author>
                            <name>'.$messages[0]->getContact()->getTrueName().'</name>
                            <uri>'.Route::urlize('blog',$messages[0]->getContact()->jid).'</uri>
                        </author>
                        <link rel="self" href="'.Route::urlize('feed',$messages[0]->getContact()->jid).'" />
                        <logo>'.$messages[0]->getContact()->getPhoto('l').'</logo>
                        
                        <generator uri="http://movim.eu/" version="'.APP_VERSION.'">
                          Movim
                        </generator>
                        
                        <id>urn:uuid:60a76c80-d399-11d9-b91C-0003939e0af6</id>';
                        
                    foreach($messages as $message) {
                        $title = $message->title;
                        if($title == null)
                            $title = trim(substr(strip_tags(html_entity_decode($message->content)), 0, 40)).'...';
                        $xml .= '
                            <entry>
                                <title>'.prepareString(html_entity_decode($title)).'</title>
                                <id>urn:uuid:'.$message->nodeid.'</id>
                                <updated>'.date('c', strtotime($message->published)).'</updated>
                                <content type="html">
                                    <![CDATA['.prepareString(html_entity_decode($message->content)).']]>
                                </content>
                            </entry>
                        ';
                    }
                $xml .= '
                    </feed>';
                echo trim($xml);
            } else {
                echo t('No public feed for this contact');
            }

        } else {
            echo t('No contact specified');
        }
    }
}
