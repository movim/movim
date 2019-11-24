<?php

/**
 * Return a human-readable date
 *
 * @param timestamp $string
 * @return string
 */
function prepareDate($time = false, $hours = true, $compact = false, $dateOnly = false)
{
    $time = $time ? $time : time();
    $t = $time + TIMEZONE_OFFSET;

    $date = '';

    $reldays = -(time() - $t - (time() % 86400)) / 86400;

    // if $reldays is within a week
    if (-7 < $reldays && $reldays <= 2) {
        if ($reldays > 1) {
            $date = '';
        } elseif (-1 < $reldays && $reldays <= 0) {
            $date = __('date.yesterday');
        } elseif (0 < $reldays && $reldays <= 1) {
            // Today
        } else {
            $date = __('date.ago', ceil(-$reldays));
        }
    } else {
        if (!$compact) {
            $date .= __('day.'.strtolower(date('l', $t))) . ', ';
        }

        $date .= date('j', $t).' '.__('month.'.strtolower(date('F', $t)));

        // Over 6 months
        if (abs($reldays) > 182) {
            $date .= gmdate(', Y', $t);
        }

        if ($compact) {
            return $date;
        }
    }

    if ($dateOnly) {
        return $date;
    }

    //if $hours option print the time
    if ($hours) {
        if ($date != '') {
            $date .= ' - ';
        }

        $date .= gmdate('H:i', $t);
    }

    return $date;
}

/**
 * Return a human-readable time
 *
 * @param timestamp $string
 * @return string
 */
function prepareTime($time = false)
{
    $time = $time ? $time : time();
    $t = $time + TIMEZONE_OFFSET;

    return gmdate('H:i', $t);
}

function toSQLDate($date)
{
    return date(MOVIM_SQL_DATE, strtotime((string)$date));
}
