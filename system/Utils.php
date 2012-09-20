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

/*
 * Return the current microtime
 */
function getTime()
{
    $a = explode (' ',microtime());
    return(double) $a[0] + $a[1];
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
    
    /*$string = preg_replace(
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
    );*/
    
    $conf = new Conf();
    $theme = $conf->getServerConfElement('theme');
    
    $path = BASE_URI . 'themes/' . $theme . '/img/smileys/';

    foreach($smileys as $key => $value) {
        $replace = '<img class="smiley" src="'.$path.$value.'">';
        $string = str_replace($key, $replace, $string);
    }

    return trim($string);
}

/**
 * Return a human-readable date 
 *
 * @param timestamp $string
 * @return string
 */
function prepareDate($time, $hours = true) {

    $dotw = array(
        1 => t('Monday'),
        2 => t('Tuesday'),
        3 => t('Wednesday'),
        4 => t('Thursday'),
        5 => t('Friday'),
        6 => t('Saturday'),
        7 => t('Friday'));
        
    $moty = array(
        1 => t('January'),
        2 => t('February'),
        3 => t('March'),
        4 => t('April'),
        5 => t('May'),
        6 => t('June'),
        7 => t('July'),
        8 => t('August'),
        9 => t('September'),
        10 => t('October'),
        11 => t('November'),
        12 => t('December'));

    $today = strtotime(date('M j, Y'));
    $reldays = ($time - $today)/86400;

    if ($reldays >= 0 && $reldays < 1) {
        $date = t('Today');
    } else if ($reldays >= 1 && $reldays < 2) {
        $date = t('Tomorrow');
    } else if ($reldays >= -1 && $reldays < 0) {
        $date = t('Yesterday');
    } else {

        if (abs($reldays) < 7) {
            if ($reldays > 0) {
                $reldays = floor($reldays);
                $date = 'In ' . $reldays . ' '.t('day') . ($reldays != 1 ? 's' : '');
            } else {
                $reldays = abs(floor($reldays));
                $date = t(' %d days ago', $reldays);
            }
        } else {
            $date = $dotw[date('N',$time ? $time : time())] .', '.date('j',$time ? $time : time()).' '.$moty[date('n',$time ? $time : time())] ;
            if (abs($reldays) > 182)
                $date .= date(', Y',$time ? $time : time());
        }
    }
    if($hours)
        $date .= ' - '. date('H:i', $time);
    
    return $date;
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

function getPresences() {
    return array(
                1 => t('Online'),
                2 => t('Away'),
                3 => t('Do Not Disturb'),
                4 => t('Extended Away'),
                5 => t('Logout')
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
