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
 * Prettify an offset
 */
function getPrettyOffset($offset)
{
    $offset_prefix = $offset < 0 ? '-' : '+';
    $offset_formatted = gmdate('H:i', abs($offset));

    return "UTC${offset_prefix}${offset_formatted}";
}

/*
 * Generate the timezone list
 */
function generateTimezoneList()
{
    $regions = [
        DateTimeZone::AFRICA,
        DateTimeZone::AMERICA,
        DateTimeZone::ANTARCTICA,
        DateTimeZone::ASIA,
        DateTimeZone::ATLANTIC,
        DateTimeZone::AUSTRALIA,
        DateTimeZone::EUROPE,
        DateTimeZone::INDIAN,
        DateTimeZone::PACIFIC,
    ];

    $timezones = [];
    foreach($regions as $region)
    {
        $timezones = array_merge( $timezones, DateTimeZone::listIdentifiers( $region ) );
    }

    $timezone_offsets_summer = [];
    $timezone_offsets_winter = [];

    foreach($timezones as $timezone)
    {
        $tz = new DateTimeZone($timezone);
        $utc = new DateTimeZone('UTC');
        $timezone_offsets_summer[$timezone] = $tz->getOffset(new DateTime('first day of August', $utc));
        $timezone_offsets_winter[$timezone] = $tz->getOffset(new DateTime('first day of December', $utc));
    }

    // sort timezone by timezone name
    ksort($timezone_offsets_summer);
    ksort($timezone_offsets_winter);

    $timezone_list = [];

    foreach($timezone_offsets_summer as $timezone => $offset)
    {
        $pretty_summer_offset = getPrettyOffset($offset);
        $pretty_winter_offset = getPrettyOffset($timezone_offsets_winter[$timezone]);

        $split = explode("/", $timezone);

        $timezone_list[$timezone] = "$split[1]/$split[0] (Summer ${pretty_summer_offset} - Winter ${pretty_winter_offset})";
    }

    ksort($timezone_list);

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
