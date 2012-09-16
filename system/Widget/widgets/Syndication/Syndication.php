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
            // We query the last messages
            $query = Post::query()
                                ->join('Contact', array('Post.jid' => 'Contact.jid'))
                                ->where(array(
                                    'Post`.`key' => $from,
                                    'Post`.`jid' => $from,
                                    'Post`.`public' => 1,
                                    'Post`.`parentid' => ''))
                                ->orderby('Post.updated', true)
                                ->limit(0, 20);
            $messages = Post::run_query($query);

            if(isset($messages[0])) {
                $xml = '
                    <?xml version="1.0" encoding="utf-8"?>
                    <feed xmlns="http://www.w3.org/2005/Atom">

                        <title>'.t("%s's feed",$messages[0][1]->getTrueName()).'</title>
                        <link href="http://example.org/"/>
                        <updated>'.date('c').'</updated>
                        <author>
                            <name>'.$messages[0][1]->getTrueName().'</name>
                        </author>
                        <id>urn:uuid:60a76c80-d399-11d9-b91C-0003939e0af6</id>';
                        
                    $redun = $messages[0][0]->nodeid->getval();
                    
                    foreach($messages as $message) {
                        if($message[0]->nodeid->getval() != $redun) {
                            $title = $message[0]->title->getval();
                            if($title == null)
                                $title = substr($message[0]->content->getval(), 0, 20).'...';
                            $xml .= '
                                <entry>
                                    <title>'.$title.'</title>
                                    <id>urn:uuid:'.$message[0]->nodeid->getval().'</id>
                                    <updated>'.date('c', strtotime($message[0]->updated->getval())).'</updated>
                                    <summary type="html"><![CDATA['.prepareString($message[0]->content->getval()).']]></summary>
                                </entry>
                            ';
                            
                            $redun = $message[0]->nodeid->getval();
                        }
                    }
                $xml .= '
                    </feed>';
                echo trim($xml);
            }
        } else {
            echo t('No contact specified');
        }
    }
}
