<?php

/*
 * Get the timezone list
 */
function getTimezoneList()
{
    global $timezones;
    require_once(HELPERS_PATH.'TimezoneList.php');
    return $timezones;
}

/*
 * Get the offset of a timezone
 */
function getTimezoneOffset($timezone)
{
    $tz = new DateTimeZone($timezone);
    $utc = new DateTimeZone('UTC'); 
    return $tz->getOffset(new DateTime('now', $utc));
}

/*
 * Generate the timezone list
 */
 function generateTimezoneList()
 {
    $regions = array(
        DateTimeZone::AFRICA,
        DateTimeZone::AMERICA,
        DateTimeZone::ANTARCTICA,
        DateTimeZone::ASIA,
        DateTimeZone::ATLANTIC,
        DateTimeZone::AUSTRALIA,
        DateTimeZone::EUROPE,
        DateTimeZone::INDIAN,
        DateTimeZone::PACIFIC,
    );

    $timezones = array();
    foreach( $regions as $region )
    {
        $timezones = array_merge( $timezones, DateTimeZone::listIdentifiers( $region ) );
    }

    $timezone_offsets = array();
    foreach( $timezones as $timezone )
    {
        $tz = new DateTimeZone($timezone);
        $utc = new DateTimeZone('UTC');
        $timezone_offsets[$timezone] = $tz->getOffset(new DateTime('now', $utc));
    }

    // sort timezone by timezone name
    ksort($timezone_offsets);

    $timezone_list = array();
    foreach( $timezone_offsets as $timezone => $offset )
    {
        $offset_prefix = $offset < 0 ? '-' : '+';
        $offset_formatted = gmdate( 'H:i', abs($offset) );

        $pretty_offset = "UTC${offset_prefix}${offset_formatted}";
        
        $split = explode("/", $timezone);

        $timezone_list[$timezone] = "$split[1]/$split[0] (${pretty_offset})";
    }
    
    asort($timezone_list);
    
    return $timezone_list;
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
