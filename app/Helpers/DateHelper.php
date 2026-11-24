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
    return date(MOVIM_SQL_DATE, strtotime((string) $date));
}

function canonicalTimezone(string $timezone): ?string
{
    if (method_exists(\IntlTimeZone::class, 'getIanaID')) {
        $iana = @\IntlTimeZone::getIanaID($timezone);
        if ($iana !== false && $iana !== null) {
            return $iana;
        }
    }

    // Fallback code for older ICU releases

    $fallbackTimezones = [
        'Africa/Asmera' => 'Africa/Asmara',
        'America/Buenos_Aires' => 'America/Argentina/Buenos_Aires',
        'America/Catamarca' => 'America/Argentina/Catamarca',
        'America/Cordoba' => 'America/Argentina/Cordoba',
        'America/Jujuy' => 'America/Argentina/Jujuy',
        'America/Mendoza' => 'America/Argentina/Mendoza',
        'America/Coral_Harbour' => 'America/Atikokan',
        'America/Indianapolis' => 'America/Indiana/Indianapolis',
        'America/Louisville' => 'America/Kentucky/Louisville',
        'America/Godthab' => 'America/Nuuk',
        'Asia/Saigon' => 'Asia/Ho_Chi_Minh',
        'Asia/Katmandu' => 'Asia/Kathmandu',
        'Asia/Calcutta' => 'Asia/Kolkata',
        'Asia/Rangoon' => 'Asia/Yangon',
        'Atlantic/Faeroe' => 'Atlantic/Faroe',
        'Europe/Kiev' => 'Europe/Kyiv',
        'Pacific/Truk' => 'Pacific/Chuuk',
        'Pacific/Enderbury' => 'Pacific/Kanton',
        'Pacific/Ponape' => 'Pacific/Pohnpei',
        'Etc/UTC' => 'UTC',
    ];

    if (array_key_exists($timezone, $fallbackTimezones)) {
        return $fallbackTimezones[$timezone];
    }

    return null;
}
