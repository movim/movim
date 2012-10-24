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
                                ->where(array(
                                    'jid' => $from,
                                    'public' => 1,
                                    'parentid' => ''))
                                ->orderby('updated', true)
                                ->limit(0, 20);
            $messages = Post::run_query($query);

            $query = Contact::query()->select()
                                       ->where(array(
                                               //'key' => $from,
                                               'jid' => $from));
            $contact = Contact::run_query($query);

            if(isset($messages[0]) && isset($contact[0])) {
                header("Content-Type: application/atom+xml; charset=UTF-8");
        
                $contact = $contact[0];
                $xml = '
                    <?xml version="1.0" encoding="utf-8"?>
                    <feed xmlns="http://www.w3.org/2005/Atom">

                        <title>'.t("%s's feed",$contact->getTrueName()).'</title>
                        <link href="http://example.org/"/>
                        <updated>'.date('c').'</updated>
                        <author>
                            <name>'.$contact->getTrueName().'</name>
                        </author>
                        <id>urn:uuid:60a76c80-d399-11d9-b91C-0003939e0af6</id>';
                        
                    foreach($messages as $message) {
                        $title = $message->title->getval();
                        if($title == null)
                            $title = substr($message->content->getval(), 0, 20).'...';
                        $xml .= '
                            <entry>
                                <title>'.$title.'</title>
                                <id>urn:uuid:'.$message->nodeid->getval().'</id>
                                <updated>'.date('c', strtotime($message->updated->getval())).'</updated>
                                <summary type="html"><![CDATA['.html_entity_decode(prepareString($message->content->getval())).']]></summary>
                            </entry>
                        ';
                        
                        $redun = $message->nodeid->getval();
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
