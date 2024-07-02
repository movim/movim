<?php

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

    return 'UTC' . $offset_prefix . $offset_formatted;
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
    foreach ($regions as $region) {
        $timezones = array_merge($timezones, DateTimeZone::listIdentifiers($region));
    }

    $timezoneOffsetsSummer = [];
    $timezoneOffsetsWinter = [];

    foreach ($timezones as $timezone) {
        $tz = new DateTimeZone($timezone);
        $utc = new DateTimeZone('UTC');
        $timezoneOffsetsSummer[$timezone] = $tz->getOffset(new DateTime('first day of August', $utc));
        $timezoneOffsetsWinter[$timezone] = $tz->getOffset(new DateTime('first day of December', $utc));
    }

    // sort timezone by timezone name
    ksort($timezoneOffsetsSummer);
    ksort($timezoneOffsetsWinter);

    $timezoneList = [];

    foreach ($timezoneOffsetsSummer as $timezone => $offset) {
        $prettySummerOffset = getPrettyOffset($offset);
        $prettyWinterOffset = getPrettyOffset($timezoneOffsetsWinter[$timezone]);

        $split = explode("/", $timezone);

        $timezoneList[$timezone] = $split[1] . '/' . $split[0] . ' (Summer ' . $prettySummerOffset . '- Winter' . $prettyWinterOffset . ')';
    }

    ksort($timezoneList);

    return $timezoneList;
}
