<?php

/**
 * @file images.php
 * This file is part of MOVIM.
 * 
 * @brief The movim's images handler.
 *
 * @author Edhelas <edhelas@gmail.com>
 *
 * @version 1.0
 * @date  22 November 2011
 *
 * Copyright (C)2010 MOVIM team
 * 
 * See the file `COPYING' for licensing information.
 */

ini_set('log_errors', 1);
ini_set('display_errors', 0);
ini_set('error_reporting', E_ALL ^ E_DEPRECATED ^ E_NOTICE);
ini_set('error_log', 'log/php.log');

require("init.php");

global $sdb;

// We load the avatar from the database and we display it
if(isset($_GET['c'])) {
    $hash = md5($_GET['c'].$_GET['size']);
    $headers = getallheaders();
    
    ob_clean();
    ob_start();
    if (ereg($hash, $headers['If-None-Match']))
    {
        header('HTTP/1.1 304 Not Modified');
        exit;
    } elseif($_GET['c'] == 'default') {
            ob_clean();
            ob_start();
            $content = file_get_contents('themes/movim/img/default.svg');
            

            header("ETag: \"{$hash}\"");
            header("Accept-Ranges: bytes");
            header("Content-type: image/svg+xml");
            header("Cache-Control: max-age=".rand(1, 5)*3600);
            header('Date: ' . gmdate('D, d M Y H:i:s', time()) . ' GMT');
            header('Expires: ' . gmdate('D, d M Y H:i:s', time()+24*60*60) . ' GMT');
            echo $content;
            exit;

     }
    
     else {
        $user = new User();
        $contact = $sdb->select('Contact', array('key' => $user->getLogin(), 'jid' => $_GET['c']));
        
        if($contact[0]->phototype != '' && $contact[0]->photobin != '') {
            if(isset($_GET['size']) && $_GET['size'] != 'normal') {
                switch ($_GET['size']) {
                    case 'm':
                        $size = 120;
                        break;
                    case 's':
                        $size = 50;
                        break;
                    case 'xs':
                        $size = 24;
                        break;
                }
                $thumb = imagecreatetruecolor($size, $size);
                $source = imagecreatefromstring(base64_decode($contact[0]->photobin));
                $width = imagesx($source);
                $height = imagesy($source);
                imagecopyresized($thumb, $source, 0, 0, 0, 0, $size, $size, $width, $height);
                
                header("ETag: \"{$hash}\"");
                header("Cache-Control: max-age=".rand(1, 5)*3600);
                header("Accept-Ranges: bytes");
                header("Content-type: image/jpeg");
                header('Date: ' . gmdate('D, d M Y H:i:s', time()) . ' GMT');
                header('Expires: ' . gmdate('D, d M Y H:i:s', time()+24*60*60) . ' GMT');
                imagejpeg($thumb, NULL, 95);
                
            } elseif(isset($_GET['size']) && $_GET['size'] == 'normal') { // The original picture
                ob_clean();
                ob_start();
                
                header("ETag: \"{$hash}\"");
                header("Cache-Control: max-age=".rand(1, 5)*3600);
                header("Accept-Ranges: bytes");
                header('Date: ' . gmdate('D, d M Y H:i:s', time()) . ' GMT');
                header('Expires: ' . gmdate('D, d M Y H:i:s', time()+24*60*60) . ' GMT');
                header("Content-type:".$contact[0]->phototype);
                echo base64_decode($contact[0]->photobin);
            }
        }
    }
}
