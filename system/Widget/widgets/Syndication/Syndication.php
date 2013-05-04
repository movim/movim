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
        if(isset($from)) {          
            $pd = new \modl\PostnDAO();
            $messages = $pd->getPublic($from);
        

            if(!empty($messages)) {
                header("Content-Type: application/atom+xml; charset=UTF-8");
                
                $xml = '
                    <?xml version="1.0" encoding="utf-8"?>
                    <feed xmlns="http://www.w3.org/2005/Atom">

                        <title>'.t("%s's feed",$messages[0]->getContact()->getTrueName()).'</title>
                        <link href="http://example.org/"/>
                        <updated>'.date('c').'</updated>
                        <author>
                            <name>'.$messages[0]->getContact()->getTrueName().'</name>
                        </author>
                        <logo>'.$messages[0]->getContact()->getPhoto('l').'</logo>
                        <id>urn:uuid:60a76c80-d399-11d9-b91C-0003939e0af6</id>';
                        
                    foreach($messages as $message) {
                        $title = $message->title;
                        if($title == null)
                            $title = substr(strip_tags(html_entity_decode($message->content)), 0, 40).'...';
                        $xml .= '
                            <entry>
                                <title>'.prepareString(html_entity_decode($title)).'</title>
                                <id>urn:uuid:'.$message->nodeid.'</id>
                                <updated>'.date('c', strtotime($message->published)).'</updated>
                                <summary type="html">
                                    <![CDATA['.prepareString(html_entity_decode($message->content)).']]>
                                </summary>
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
