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
function prepareDate($time, $hours = true) {    
    $dotw = getDays();
        
    $moty = getMonths();

    $today = strtotime(date('M j, Y'));

    // We fix the timezone
    $time = $time + 3600*(int)getTimezoneCorrection();

    $reldays = ($time - $today)/86400;

    if ($reldays >= 0 && $reldays < 1) {
        $date = __('date.today');
    } else if ($reldays >= 1 && $reldays < 2) {
        $date = __('date.tomorrow');
    } else if ($reldays >= -1 && $reldays < 0) {
        $date = __('date.yesterday');
    } else {

        if (abs($reldays) < 7) {
            if ($reldays > 0) {
                $reldays = floor($reldays);
                $date = 'In ' . $reldays . ' '.__('date.day') . ($reldays != 1 ? 's' : '');
            } else {
                $reldays = abs(floor($reldays));
                $date = __('date.ago', $reldays);
            }
        } else {
            $date = $dotw[date('N',$time ? $time : time())] .', '.date('j',$time ? $time : time()).' '.$moty[date('n',$time ? $time : time())] ;
            if (abs($reldays) > 182)
                $date .= date(', Y',$time ? $time : time());
        }
    }
    if($hours)
        $date .= ' - '. date('H:i', $time);
        
    if($time)
        return $date;
}
