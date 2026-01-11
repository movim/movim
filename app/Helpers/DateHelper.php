<?php

/**
 * Return the offset in minutes between the user timezone and UTC
 */
function getTimezoneOffset(string $timezone): int
{
    return (new DateTimeZone($timezone)
    )->getOffset(new DateTime('now', new DateTimeZone('UTC')));
}

function toSQLDate($date): string
{
    return date(MOVIM_SQL_DATE, strtotime((string)$date));
}
