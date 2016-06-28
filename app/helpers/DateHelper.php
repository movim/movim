<?php

/**
 * Return a human-readable week
 * @return string
 */
function getDays() {
    return array(
        1 => __('day.monday'),
        2 => __('day.tuesday'),
        3 => __('day.wednesday'),
        4 => __('day.thursday'),
        5 => __('day.friday'),
        6 => __('day.saturday'),
        7 => __('day.sunday'));
}
/**
 * Return a human-readable year
 * @return string
 */
function getMonths() {
    return array(
        1 => __('month.january'),
        2 => __('month.february'),
        3 => __('month.march'),
        4 => __('month.april'),
        5 => __('month.may'),
        6 => __('month.june'),
        7 => __('month.july'),
        8 => __('month.august'),
        9 => __('month.september'),
        10 => __('month.october'),
        11 => __('month.november'),
        12 => __('month.december'));
}

function getTimezoneCorrection() {
    $timezones = getTimezoneList();
    return $timezones[date_default_timezone_get()];
}

/**
 * Return a human-readable date
 *
 * @param timestamp $string
 * @return string
 */
function prepareDate($time, $hours = true, $compact = false) {
    // If the time is empty, return nothing
    if(empty($time)) return '';

    // We had the server timezone
    $time = $time + TIMEZONE_OFFSET;

    $t = $time ? $time : time();

    $date = '';

    $reldays = ((time() - $t)-(time()%86400))/86400;
    // if $time is within a week
    if($reldays < 7 && $reldays >= -2){
        //if $time is today or yesterday
        if($reldays < -1) {
            $date = __('date.tomorrow');
        } else if ($reldays <= 0) {
            //$date = __('date.today');
        } else if ($reldays <= 1) {
            $date = __('date.yesterday');
        }
        //else print date "ago"
        else {
            $date = __('date.ago', ceil($reldays));

            //if($compact) return $date;
        }
    }
    //else print full date
    else {
        $date = '';

        if(!$compact)
            $date .= __('day.'.strtolower(date('l', $t))) . ', ';

        $date .= date('j', $t).' '.__('month.'.strtolower(date('F', $t)));

        //if over 6months print year
        if (abs($reldays) > 182)
            $date .= date(', Y', $t);

        if($compact) return $date;
    }
    //if $hours option print the time
    if($hours) {
        if($date != '') {
            $date .= ' - ';
        }
        $date .= date('H:i', $time);
    }
    return $date;
}

