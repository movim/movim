<?php

/*
 * Get the timezone list
 */
 function getTimezoneList()
 {
    return array(
        'Etc/GMT+12' => -12.00,
        'Etc/GMT+11' => -11.00,
        'Etc/GMT+10' => -10.00,
        'Etc/GMT+9' => -9.00,
        'Etc/GMT+8' => -8.00,
        'Etc/GMT+7' => -7.00,
        'Etc/GMT+6' => -6.00,
        'Etc/GMT+5' => -5.00,
        'America/Caracas' => -4.30,
        'Etc/GMT+4' => -4.00,
        'America/St_Johns' => -3.30,
        'Etc/GMT+3' => -3.00,
        'Etc/GMT+2' => -2.00,
        'Etc/GMT+1' => -1.00,
        'Etc/GMT' => 0,
        'Etc/GMT-1' => 1.00,
        'Etc/GMT-2' => 2.00,
        'Etc/GMT-3' => 3.00,
        'Asia/Tehran' => 3.30,
        'Etc/GMT-4' => 4.00,
        'Etc/GMT-5' => 5.00,
        'Asia/Kolkata' => 5.30,
        'Asia/Katmandu' => 5.45,
        'Etc/GMT-6' => 6.00,
        'Asia/Rangoon' => 6.30,
        'Etc/GMT-7' => 7.00,
        'Etc/GMT-8' => 8.00,
        'Etc/GMT-9' => 9.00,
        'Australia/Darwin' => 9.30,
        'Etc/GMT-10' => 10.00,
        'Etc/GMT-11' => 11.00,
        'Etc/GMT-12' => 12.00,
        'Etc/GMT-13' => 13.00);
 }
 

/*
 * Get the user local timezone
 */
function getLocalTimezone()
{
    date_default_timezone_set('UTC');
    $iTime = time();
    $arr = localtime($iTime);
    $arr[5] += 1900;
    $arr[4]++;
    $iTztime = gmmktime($arr[2], $arr[1], $arr[0], $arr[4], $arr[3], $arr[5]);
    $offset = doubleval(($iTztime-$iTime)/(60*60));
    $zonelist = getTimezoneList();

    $index = array_keys($zonelist, $offset);
    if(sizeof($index)!=1)
        return false;
    return $index[0];
}
