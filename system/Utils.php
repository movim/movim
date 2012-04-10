<?php

/**
 * @file Utils.php
 * This file is part of PROJECT.
 * 
 * @brief Description
 *
 * @author Etenil <etenil@etenilsrealm.nl>
 *
 * @version 1.0
 * @date 20 February 2011
 *
 * Copyright (C)2011 Etenil
 * 
 * All rights reserved.
 */

// Handy.
function println($string)
{
    $args = func_get_args();
    echo call_user_func_array('sprintf', $args) . PHP_EOL;
}

function sprintln($string)
{
    $args = func_get_args();
    return call_user_func_array('sprintf', $args) . PHP_EOL;
}

/**
 * Prepare the string (add the a to the links and show the smileys)
 *
 * @param string $string
 * @return string
 */
function prepareString($string) {
    $smileys = 
        array(
            ':okay:' => 'okay.gif',
            'O:)' => 'ange.gif',
            'O:-)' => 'ange.gif',
            ':)' => 'smile.gif',
            ':-)' => 'smile.gif',
            ':(' => 'frown.gif',
            ':o' => 'redface.gif',
            ':love:' => 'love.gif',
            '<3' => 'love.gif',
            ':D' => 'biggrin.gif',
            ':d' => 'biggrin.gif',
            ':p' => 'tongue.gif',
            ':P' => 'tongue.gif',
            ':-P' => 'tongue.gif',
            ' :/' => 'bof.gif', // Here we add a space to prevent URL parse error in the second part of the function
            ';)' => 'wink.gif',
            'B)' => 'sol.gif',
            ":'(" => 'cry.gif',
            ':trolldad:' => 'trolldad.png',
            ':epic:' => 'epic.png',
            ':aloneyeah:' => 'aloneyeah.png',
            ':fapfap:' => 'fapfap.png',
            ':megusta:' => 'gusta.png',
            ':trollface:' => 'trollface.png',
            ':troll:' => 'trollface.png',
            ':lol:' => 'trollol.png',
        );
    
    $string = preg_replace(
        array(
            '/(?(?=<a[^>]*>.+<\/a>)
            (?:<a[^>]*>.+<\/a>)
            |
            ([^="\']?)((?:https?|ftp|bf2|):\/\/[^<> \n\r]+)
            )/iex',
            '/<a([^>]*)target="?[^"\']+"?/i',
            '/<a([^>]+)>/i',
            '/(^|\s)(www.[^<> \n\r]+)/iex',
            '/(([_A-Za-z0-9-]+)(\\.[_A-Za-z0-9-]+)*@([A-Za-z0-9-]+)
            (\\.[A-Za-z0-9-]+)*)/iex'
        ),
        array(
            "stripslashes((strlen('\\2')>0?'\\1<a href=\"\\2\">\\2</a>\\3':'\\0'))",
            '<a\\1',
            '<a\\1 target="_blank">',
            "stripslashes((strlen('\\2')>0?'\\1<a href=\"http://\\2\">\\2</a>\\3':'\\0'))",
            "stripslashes((strlen('\\2')>0?'<a href=\"mailto:\\0\">\\0</a>':'\\0'))"
        ),
        $string
    );
    
    $conf = new Conf();
    $theme = $conf->getServerConfElement('theme');
    
    $path = BASE_URI . 'themes/' . $theme . '/img/smileys/';

    foreach($smileys as $key => $value) {
        $replace = '<img src="'.$path.$value.'">';
        $string = str_replace($key, $replace, $string);
    }

    return $string;
}

/**
 * Return a human-readable date 
 *
 * @param timestamp $string
 * @return string
 */
function prepareDate($time) {

    $today = strtotime(date('M j, Y'));
    $reldays = ($time - $today)/86400;

    if ($reldays >= 0 && $reldays < 1) {
        return t('Today') .' - '. date('H:i', $time);
    } else if ($reldays >= 1 && $reldays < 2) {
        return t('Tomorrow') .' - '. date('H:i', $time);
    } else if ($reldays >= -1 && $reldays < 0) {
        return t('Yesterday') .' - '. date('H:i', $time);
    }

    if (abs($reldays) < 7) {
        if ($reldays > 0) {
            $reldays = floor($reldays);
            return 'In ' . $reldays . ' '.t('day') . ($reldays != 1 ? 's' : '');
        } else {
            $reldays = abs(floor($reldays));
            return t(' %d days ago', $reldays); // . ' '.t('day') . ($reldays != 1 ? 's' : '') . ' ago';
        }
    }
    if (abs($reldays) < 182) {
        return date('l, j F',$time ? $time : time());
    } else {
        return date('l, j F, Y',$time ? $time : time());
    }
}

/**
 * Generate a ramdom hash
 *
 * @return string
 */
function generateHash(){
    $result = "";
    $charPool = '0123456789abcdefghijklmnopqrstuvwxyz';

    for($p = 0; $p<15; $p++)
        $result .= $charPool[mt_rand(0,strlen($charPool)-1)];

    return sha1($result);
}

/**
 * Return the list of gender
 */
function getGender() {
    return array('N' => t('None'),
                    'M' => t('Male'),
                    'F' => t('Female'),
                    'O' => t('Other')
                    );
}

/**
 * Return the list of marital status
 */
function getMarital() {
    return array('none' => t('None'),
                    'single' => t('Single'),
                    'relationship' => t('In a relationship'),
                    'married' => t('Married'),
                    'divorced' => t('Divorced'),
                    'widowed' => t('Widowed'),
                    'cohabiting' => t('Cohabiting'),
                    'union' => t('Civil Union')
                    );                      
}

/**
 * Check the current Jid
 *
 * @param string $jid
 * @return bool
 */
function checkJid($jid)
{
    return filter_var($jid, FILTER_VALIDATE_EMAIL);
}

function movim_log($log) {
	ob_start();
//    var_dump($log);
	print_r($log);
	$dump = ob_get_clean();
	$fh = fopen(BASE_PATH . 'log/movim.log', 'w');
	fwrite($fh, $dump);
	fclose($fh);
}

?>
