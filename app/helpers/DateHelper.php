<?php

/**
 * Return a human-readable week
 * @return string
 */
function getDays() {
    return [
        1 => __('day.monday'),
        2 => __('day.tuesday'),
        3 => __('day.wednesday'),
        4 => __('day.thursday'),
        5 => __('day.friday'),
        6 => __('day.saturday'),
        7 => __('day.sunday')
    ];
}
/**
 * Return a human-readable year
 * @return string
 */
function getMonths() {
    return [
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
        12 => __('month.december')
    ];
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
function prepareDate($time = false, $hours = true, $compact = false) {
    $time = $time ? $time : time();
    $t = $time + TIMEZONE_OFFSET;

    $date = '';

    $reldays = -(time() - $t - (time() % 86400)) / 86400;

    // if $reldays is within a week
    if (-7 < $reldays && $reldays <= 2) {
        if($reldays > 1) {
            $date = '';
        } else if (-1 < $reldays && $reldays <= 0) {
            $date = __('date.yesterday');
        } else if (0 < $reldays && $reldays <= 1) {
            // Today
        } else {
            $date = __('date.ago', ceil(-$reldays));
        }
    } else {
        if(!$compact) {
            $date .= __('day.'.strtolower(date('l', $t))) . ', ';
        }

        $date .= date('j', $t).' '.__('month.'.strtolower(date('F', $t)));

        // Over 6 months
        if (abs($reldays) > 182) {
            $date .= gmdate(', Y', $t);
        }

        if($compact) return $date;
    }
    //if $hours option print the time
    if($hours) {
        if($date != '') {
            $date .= ' - ';
        }

        $date .= gmdate('H:i', $t);
    }

    return $date;
}

