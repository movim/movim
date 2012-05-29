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

function display_image($hash, $type) {
    ob_clean();
    ob_start();
    header("ETag: \"{$hash}\"");
    header("Accept-Ranges: bytes");
    header("Content-type: ".$type);
    header("Cache-Control: max-age=".rand(1, 5)*3600);
    header('Date: ' . gmdate('D, d M Y H:i:s', time()) . ' GMT');
    header('Expires: ' . gmdate('D, d M Y H:i:s', time()+24*60*60) . ' GMT');
}

if (!function_exists('getallheaders')) {
        function getallheaders() {
            foreach($_SERVER as $key=>$value) {
                if (substr($key,0,5)=="HTTP_") {
                    $key=str_replace(" ","-",ucwords(strtolower(str_replace("_"," ",substr($key,5)))));
                    $out[$key]=$value;
                }else{
                    $out[$key]=$value;
        }
            }
            return $out;
        }
} 

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
            
            display_image($hash, "image/svg+xml");
            echo $content;
            exit;

     }
    
     else {
        $user = new User();
        $contact = $sdb->select('Contact', array('key' => $user->getLogin(), 'jid' => $_GET['c']));
        
        if($contact[0]->phototype != '' && 
           $contact[0]->photobin != '' && 
           $contact[0]->phototype != 'f' && 
           $contact[0]->photobin != 'f') {
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
                $white = imagecolorallocate($thumb, 255, 255, 255);
                imagefill($thumb, 0, 0, $white);
                
                $source = imagecreatefromstring(base64_decode($contact[0]->photobin));
                
                $width = imagesx($source);
                $height = imagesy($source);
                
                if($width >= $height) {
                    // For landscape images
                    $x_offset = ($width - $height) / 2;
                    $y_offset = 0;
                    $square_size = $width - ($x_offset * 2);
                } else {
                    // For portrait and square images
                    $x_offset = 0;
                    $y_offset = ($height - $width) / 2;
                    $square_size = $height - ($y_offset * 2);
                }
                
                if($source) {
                    imagecopyresampled($thumb, $source, 0, 0, $x_offset, $y_offset, $size, $size, $square_size, $square_size);
                    
                    display_image($hash, "image/jpeg");
                    imagejpeg($thumb, NULL, 95);
                }
                
            } elseif(isset($_GET['size']) && $_GET['size'] == 'normal') { // The original picture
                display_image($hash, $contact[0]->phototype);
                echo base64_decode($contact[0]->photobin);
            }
        }
    }
}

// Closing db (dirty...)
global $sdb;
$sdb->close();
