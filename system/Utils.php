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
 * Prepare the string (add the a the the links)
 *
 * @param string $string
 * @return string
 */
function prepareString($string) {
    preg_match('/(http:\/\/[^\s]+)/', $string, $text);
    $hypertext = "<a target=\"_blank\" href=\"". $text[0] . "\">" . $text[0] . "</a>";
    return preg_replace('/(http:\/\/[^\s]+)/', $hypertext, $string);
}

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
            return $reldays . ' '.t('day') . ($reldays != 1 ? 's' : '') . ' ago';
        }
    }
    if (abs($reldays) < 182) {
        return date('l, j F',$time ? $time : time());
    } else {
        return date('l, j F, Y',$time ? $time : time());
    }
}

?>
