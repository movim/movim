<?php

use Monolog\Formatter\LineFormatter;
use Monolog\Logger;
use Monolog\Handler\SyslogHandler;
use Monolog\Handler\StreamHandler;
use Movim\Image;
use Movim\ImageSize;
use Moxl\Xec\Payload\Packet;
use React\Http\Message\Response;
use React\Promise\Promise;
use React\Promise\PromiseInterface;

use function React\Async\await;

/**
 * Me
 */
function me(bool $reload = false)
{
    return \App\User::me($reload);
}

/**
 * Log an error
 */
function logError(string|Stringable $logs)
{
    $log = new Logger('movim');
    $log->pushHandler(new SyslogHandler('movim'));

    $stream = new StreamHandler(config('paths.log') . '/errors.log');
    $stream->setFormatter(new LineFormatter(null, null, true, true));
    $log->pushHandler($stream);

    $log->error($logs);
}

/**
 * Log an info
 */
function logInfo(string|Stringable $logs)
{
    if (config('daemon.verbose')) {
        $log = new Logger('movim');
        $log->pushHandler(new SyslogHandler('movim'));

        $stream = new StreamHandler(config('paths.log') . '/info.log');
        $stream->setFormatter(new LineFormatter(null, null, true));
        $log->pushHandler($stream);

        $log->info($logs);
    }
}

/**
 * Log a string, only used for debug purposes
 */
function logDebug($logs)
{
    $log = new Logger('movim');
    $log->pushHandler(new StreamHandler(config('paths.log') . '/debug.log'));
    if (is_array($logs)) {
        $log->debug('', $logs);
    } else {
        $log->debug($logs);
    }
}

/**
 * Return a configuration variable
 */
function config(string $key, $default = null)
{
    $path = explode('.', $key);
    $config = require(CONFIG_PATH . $path[0] . '.php');

    if (!isset($path[1])) return $config;

    if (array_key_exists($path[1], $config) && !empty($config[$path[1]])) {
        $casted = null;

        switch ($config[$path[1]]) {
            case 'true':
                $casted = true;
                break;

            case 'false':
                $casted = false;
                break;

            default:
                $casted = $config[$path[1]];
                break;
        }

        return $casted;
    }

    return $default;
}

/**
 * Check if Opcache is enabled
 */
function isOpcacheEnabled(): bool
{
    return is_array(opcache_get_status());
}

/**
 * List compilable Opcache files
 */
function listOpcacheCompilableFiles(): array
{
    $files = [];

    foreach (['vendor', 'app', 'src'] as $dir) {
        $directory = new \RecursiveDirectoryIterator(DOCUMENT_ROOT . '/' . $dir);
        $iterator = new \RecursiveIteratorIterator($directory);
        $regex = new \RegexIterator($iterator, '/^.+\.php$/i', \RecursiveRegexIterator::GET_MATCH);

        foreach ($regex as $key => $file) {
            array_push($files, $file[0]);
        }
    }

    return $files;
}

/**
 * Compile main files in Opcache
 */
function compileOpcache()
{
    error_reporting(0);
    foreach (listOpcacheCompilableFiles() as $file) {
        if (opcache_is_script_cached($file)) {
            yield @opcache_invalidate($file, true);
        } else {
            yield @opcache_compile_file($file);
        }
    }
    error_reporting(1);
}

/**
 * Check if the session exists
 */
function isLogged()
{
    return (bool)(\Movim\Session::instance())->get('jid');
}

/**
 * Return the list of client types
 */
function getClientTypes()
{
    return [
        'bot'           => __('client.bot'),
        'console'       => __('client.console'),
        'pc'            => __('client.desktop'),
        'phone'         => __('client.phone'),
        'gateway'       => __('client.gateway'),
        'handheld'      => __('client.phone'),
        'web'           => __('client.web'),
        'registered'    => __('client.registered')
    ];
}

/**
 * Resolve infos from a Posts collection
 */
function resolveInfos($postCollection)
{
    $serverNodes = $postCollection->map(function ($item) {
        return ['server' => $item->server, 'node' => $item->node];
    })->unique(fn($item) => $item['server'] . $item['node']);

    if ($serverNodes->isNotEmpty()) {
        $first = $serverNodes->first();
        $infos = \App\Info::where([
            'server' => $first['server'],
            'node' => $first['node'],
        ]);

        $serverNodes->skip(1)->each(function ($serverNode) use ($infos) {
            $infos->orWhere([
                'server' => $serverNode['server'],
                'node' => $serverNode['node'],
            ]);
        });

        $infos = $infos->get()->keyBy(fn($item) => $item['server'] . $item['node']);

        $postCollection->map(function ($item) use ($infos) {
            $item->info = $infos->get($item->server . $item->node);
            return $item;
        });

        return $postCollection;
    }
}

/**
 * Get required PHP extensions
 */
function requiredExtensions(): array
{
    $extensions = [
        'dom',
        'imagick',
        'mbstring',
        'openssl',
        'pdo',
        'simplexml',
        'xml',
    ];

    // ext-json is included in PHP since 8.0
    if (version_compare(PHP_VERSION, '8.0.0') < 0) {
        array_push($extensions, 'json');
    }

    if (config('database.driver') == 'mysql') {
        array_push($extensions, 'mysqlnd');
        array_push($extensions, 'mysqli');
        array_push($extensions, 'pdo_mysql');
    } else {
        array_push($extensions, 'pdo_pgsql');
    }

    // Optional extension
    if (extension_loaded('bcmath')) {
        array_push($extensions, 'bcmath');
    }

    return $extensions;
}

/**
 *  Form to array
 */
function formToArray(stdClass $form): array
{
    $values = [];

    foreach ($form as $key => $value) {
        $values[$key] = $value->value;
    }

    return $values;
}

/**
 * Return the picture or fallback to the placeholder
 */
function getPicture(?string $key, string $placeholder, ImageSize $size = ImageSize::M): string
{
    [$width, $height] =  match ($size) {
        ImageSize::XXL => [1280, 300],
        ImageSize::XL => [512, false],
        ImageSize::L => [210, false],
        ImageSize::M => [120, false],
        ImageSize::S => [50, false],
        ImageSize::O => [false, false],
    };

    return (!empty($key) && $url = Image::getOrCreate($key, $width, $height))
        ? $url
        : avatarPlaceholder($placeholder);
}

/**
 * Return a XEP to namespace association
 */
function getXepNamespace()
{
    return [
        '0004' => ['name' => 'Data Forms',             'category' => 'client',     'ns' => 'jabber:x:data'],
        '0012' => ['name' => 'Last Activity',          'category' => 'chat',       'ns' => 'jabber:iq:last'],
        '0030' => ['name' => 'Service Discovery',      'category' => 'client',     'ns' => 'http://jabber.org/protocol/disco#info'],
        '0045' => ['name' => 'Multi-User Chat',        'category' => 'chat',       'ns' => 'http://jabber.org/protocol/muc'],
        '0050' => ['name' => 'Ad-Hoc Commands',        'category' => 'client',     'ns' => 'http://jabber.org/protocol/commands'],
        '0054' => ['name' => 'vcard-temp',             'category' => 'client',     'ns' => 'vcard-temp'],
        '0071' => ['name' => 'XHTML-IM',               'category' => 'chat',       'ns' => 'http://jabber.org/protocol/xhtml-im'],
        '0080' => ['name' => 'User Location',          'category' => 'profile',    'ns' => 'http://jabber.org/protocol/geoloc'],
        '0084' => ['name' => 'User Avatar',            'category' => 'profile',    'ns' => 'urn:xmpp:avatar:data'],
        '0085' => ['name' => 'Chat State Notifications', 'category' => 'chat',     'ns' => 'http://jabber.org/protocol/chatstates'],
        '0092' => ['name' => 'Software Version',       'category' => 'client',     'ns' => 'jabber:iq:version'],
        '0107' => ['name' => 'User Mood',              'category' => 'profile',    'ns' => 'http://jabber.org/protocol/mood'],
        '0108' => ['name' => 'User Activity',          'category' => 'profile',    'ns' => 'http://jabber.org/protocol/activity'],
        '0115' => ['name' => 'Entity Capabilities',    'category' => 'client',     'ns' => 'http://jabber.org/protocol/caps'],
        '0118' => ['name' => 'User Tune',              'category' => 'profile',    'ns' => 'http://jabber.org/protocol/tune'],
        '0124' => ['name' => 'Bidirectional-streams Over Synchronous HTTP (BOSH]', 'category' => 'client',    'ns' => 'http://jabber.org/protocol/httpbind'],
        '0152' => ['name' => 'Reachability Addresses', 'category' => 'client',     'ns' => 'urn:xmpp:reach:0'],
        '0166' => ['name' => 'Jingle',                 'category' => 'jingle',     'ns' => 'urn:xmpp:jingle:1'],
        '0167' => ['name' => 'Jingle RTP Sessions',    'category' => 'jingle',     'ns' => 'urn:xmpp:jingle:apps:rtp:1'],
        '0172' => ['name' => 'User Nickname',          'category' => 'profile',    'ns' => 'http://jabber.org/protocol/nick'],
        '0176' => ['name' => 'Jingle ICE-UDP Transport Method', 'category' => 'jingle',     'ns' => 'urn:xmpp:jingle:transports:ice-udp:1'],
        '0177' => ['name' => 'Jingle Raw UDP Transport Method', 'category' => 'jingle',     'ns' => 'urn:xmpp:jingle:transports:raw-udp:1'],
        '0184' => ['name' => 'Message Delivery Receipts', 'category' => 'chat',    'ns' => 'urn:xmpp:receipts'],
        '0186' => ['name' => 'Invisible Command',      'category' => 'chat',       'ns' => 'urn:xmpp:invisible:0'],
        '0199' => ['name' => 'XMPP Ping',              'category' => 'client',     'ns' => 'urn:xmpp:ping'],
        '0202' => ['name' => 'Entity Time',            'category' => 'client',     'ns' => 'urn:xmpp:time'],
        '0224' => ['name' => 'Attention',              'category' => 'chat',       'ns' => 'urn:xmpp:attention:0'],
        '0231' => ['name' => 'Bits of Binary',         'category' => 'chat',       'ns' => 'urn:xmpp:bob'],
        '0234' => ['name' => 'Jingle File Transfer',   'category' => 'jingle',     'ns' => 'urn:xmpp:jingle:apps:file-transfer:4'],
        '0249' => ['name' => 'Direct MUC Invitations', 'category' => 'chat',       'ns' => 'jabber:x:conference'],
        '0277' => ['name' => 'Microblogging over XMPP', 'category' => 'social',    'ns' => 'urn:xmpp:microblog:0'],
        '0280' => ['name' => 'Message Carbons',        'category' => 'chat',       'ns' => 'urn:xmpp:carbons:2'],
        '0292' => ['name' => 'vCard4 Over XMPP',       'category' => 'profile',    'ns' => 'urn:xmpp:vcard4'],
        '0301' => ['name' => 'In-Band Real Time Text', 'category' => 'chat',       'ns' => 'urn:xmpp:rtt:0'],
        '0308' => ['name' => 'Last Message Correction', 'category' => 'chat',      'ns' => 'urn:xmpp:message-correct:0'],
        '0320' => ['name' => 'Use of DTLS-SRTP in Jingle Sessions', 'category' => 'jingle',     'ns' => 'urn:xmpp:jingle:apps:dtls:0'],
        '0327' => ['name' => 'Rayo', 'category' => 'rayo',       'ns' => 'urn:xmpp:rayo:0'],
        '0330' => ['name' => 'Pubsub Subscription',    'category' => 'social',     'ns' => 'urn:xmpp:pubsub:subscription'],
        '0332' => ['name' => 'HTTP over XMPP transport', 'category' => 'client',   'ns' => 'urn:xmpp:http'],
        '0333' => ['name' => 'Chat Markers', 'category' => 'chat', 'ns' => 'urn:xmpp:chat-markers:0'],
        '0337' => ['name' => 'Event Logging over XMPP', 'category' => 'client',    'ns' => 'urn:xmpp:eventlog'],
        '0338' => ['name' => 'Jingle Grouping Framework', 'category' => 'jingle',  'ns' => 'urn:ietf:rfc:5888'],
        '0339' => ['name' => 'Source-Specific Media Attributes in Jingle', 'category' => 'jingle',     'ns' => 'urn:ietf:rfc:5576'],
        '0340' => ['name' => 'COnferences with LIghtweight BRIdging (COLIBRI]', 'category' => 'jingle',     'ns' => 'http://jitsi.org/protocol/colibri'],
        '0341' => ['name' => 'Rayo CPA', 'category' => 'rayo',       'ns' => 'urn:xmpp:rayo:cpa:0'],
        '0342' => ['name' => 'Rayo Fax', 'category' => 'rayo',       'ns' => 'urn:xmpp:rayo:fax:1'],
        '0348' => ['name' => 'Signing Forms', 'category' => 'client',     'ns' => 'urn:xmpp:xdata:signature:oauth1'],
        '0390' => ['name' => 'Entity Capabilities 2.0', 'category' => 'client',     'ns' => 'urn:xmpp:caps:optimize'],
        '0391' => ['name' => 'Jingle Encrypted Transports', 'category' => 'jingle',     'ns' => 'urn:xmpp:jingle:jet:0'],
    ];
}

/**
 * Return a list of all the country
 */
function getCountries()
{
    return [
        'AF' => 'Afghanistan',
        'AX' => 'Aland Islands',
        'AL' => 'Albania',
        'DZ' => 'Algeria',
        'AS' => 'American Samoa',
        'AD' => 'Andorra',
        'AO' => 'Angola',
        'AI' => 'Anguilla',
        'AQ' => 'Antarctica',
        'AG' => 'Antigua and Barbuda',
        'AR' => 'Argentina',
        'AM' => 'Armenia',
        'AW' => 'Aruba',
        'AU' => 'Australia',
        'AT' => 'Austria',
        'AZ' => 'Azerbaijan',
        'BS' => 'Bahamas The',
        'BH' => 'Bahrain',
        'BD' => 'Bangladesh',
        'BB' => 'Barbados',
        'BY' => 'Belarus',
        'BE' => 'Belgium',
        'BZ' => 'Belize',
        'BJ' => 'Benin',
        'BM' => 'Bermuda',
        'BT' => 'Bhutan',
        'BO' => 'Bolivia',
        'BA' => 'Bosnia and Herzegovina',
        'BW' => 'Botswana',
        'BV' => 'Bouvet Island (Bouvetoya)',
        'BR' => 'Brazil',
        'IO' => 'British Indian Ocean Territory (Chagos Archipelago)',
        'VG' => 'British Virgin Islands',
        'BN' => 'Brunei Darussalam',
        'BG' => 'Bulgaria',
        'BF' => 'Burkina Faso',
        'BI' => 'Burundi',
        'KH' => 'Cambodia',
        'CM' => 'Cameroon',
        'CA' => 'Canada',
        'CV' => 'Cape Verde',
        'KY' => 'Cayman Islands',
        'CF' => 'Central African Republic',
        'TD' => 'Chad',
        'CL' => 'Chile',
        'CN' => 'China',
        'CX' => 'Christmas Island',
        'CC' => 'Cocos (Keeling) Islands',
        'CO' => 'Colombia',
        'KM' => 'Comoros The',
        'CD' => 'Congo',
        'CG' => 'Congo The',
        'CK' => 'Cook Islands',
        'CR' => 'Costa Rica',
        'CI' => 'Cote d\'Ivoire',
        'HR' => 'Croatia',
        'CU' => 'Cuba',
        'CY' => 'Cyprus',
        'CZ' => 'Czech Republic',
        'DK' => 'Denmark',
        'DJ' => 'Djibouti',
        'DM' => 'Dominica',
        'DO' => 'Dominican Republic',
        'EC' => 'Ecuador',
        'EG' => 'Egypt',
        'SV' => 'El Salvador',
        'GQ' => 'Equatorial Guinea',
        'ER' => 'Eritrea',
        'EE' => 'Estonia',
        'ET' => 'Ethiopia',
        'FO' => 'Faroe Islands',
        'FK' => 'Falkland Islands (Malvinas)',
        'FJ' => 'Fiji The Fiji Islands',
        'FI' => 'Finland',
        'FR' => 'France, French Republic',
        'GF' => 'French Guiana',
        'PF' => 'French Polynesia',
        'TF' => 'French Southern Territories',
        'GA' => 'Gabon',
        'GM' => 'Gambia The',
        'GE' => 'Georgia',
        'DE' => 'Germany',
        'GH' => 'Ghana',
        'GI' => 'Gibraltar',
        'GR' => 'Greece',
        'GL' => 'Greenland',
        'GD' => 'Grenada',
        'GP' => 'Guadeloupe',
        'GU' => 'Guam',
        'GT' => 'Guatemala',
        'GG' => 'Guernsey',
        'GN' => 'Guinea',
        'GW' => 'Guinea-Bissau',
        'GY' => 'Guyana',
        'HT' => 'Haiti',
        'HM' => 'Heard Island and McDonald Islands',
        'VA' => 'Holy See (Vatican City State)',
        'HN' => 'Honduras',
        'HK' => 'Hong Kong',
        'HU' => 'Hungary',
        'IS' => 'Iceland',
        'IN' => 'India',
        'ID' => 'Indonesia',
        'IR' => 'Iran',
        'IQ' => 'Iraq',
        'IE' => 'Ireland',
        'IM' => 'Isle of Man',
        'IL' => 'Israel',
        'IT' => 'Italy',
        'JM' => 'Jamaica',
        'JP' => 'Japan',
        'JE' => 'Jersey',
        'JO' => 'Jordan',
        'KZ' => 'Kazakhstan',
        'KE' => 'Kenya',
        'KI' => 'Kiribati',
        'KP' => 'Korea',
        'KR' => 'Korea',
        'KW' => 'Kuwait',
        'KG' => 'Kyrgyz Republic',
        'LA' => 'Lao',
        'LV' => 'Latvia',
        'LB' => 'Lebanon',
        'LS' => 'Lesotho',
        'LR' => 'Liberia',
        'LY' => 'Libyan Arab Jamahiriya',
        'LI' => 'Liechtenstein',
        'LT' => 'Lithuania',
        'LU' => 'Luxembourg',
        'MO' => 'Macao',
        'MK' => 'Macedonia',
        'MG' => 'Madagascar',
        'MW' => 'Malawi',
        'MY' => 'Malaysia',
        'MV' => 'Maldives',
        'ML' => 'Mali',
        'MT' => 'Malta',
        'MH' => 'Marshall Islands',
        'MQ' => 'Martinique',
        'MR' => 'Mauritania',
        'MU' => 'Mauritius',
        'YT' => 'Mayotte',
        'MX' => 'Mexico',
        'FM' => 'Micronesia',
        'MD' => 'Moldova',
        'MC' => 'Monaco',
        'MN' => 'Mongolia',
        'ME' => 'Montenegro',
        'MS' => 'Montserrat',
        'MA' => 'Morocco',
        'MZ' => 'Mozambique',
        'MM' => 'Myanmar',
        'NA' => 'Namibia',
        'NR' => 'Nauru',
        'NP' => 'Nepal',
        'AN' => 'Netherlands Antilles',
        'NL' => 'Netherlands The',
        'NC' => 'New Caledonia',
        'NZ' => 'New Zealand',
        'NI' => 'Nicaragua',
        'NE' => 'Niger',
        'NG' => 'Nigeria',
        'NU' => 'Niue',
        'NF' => 'Norfolk Island',
        'MP' => 'Northern Mariana Islands',
        'NO' => 'Norway',
        'OM' => 'Oman',
        'PK' => 'Pakistan',
        'PW' => 'Palau',
        'PS' => 'Palestinian Territory',
        'PA' => 'Panama',
        'PG' => 'Papua New Guinea',
        'PY' => 'Paraguay',
        'PE' => 'Peru',
        'PH' => 'Philippines',
        'PN' => 'Pitcairn Islands',
        'PL' => 'Poland',
        'PT' => 'Portugal, Portuguese Republic',
        'PR' => 'Puerto Rico',
        'QA' => 'Qatar',
        'RE' => 'Reunion',
        'RO' => 'Romania',
        'RU' => 'Russian Federation',
        'RW' => 'Rwanda',
        'BL' => 'Saint Barthelemy',
        'SH' => 'Saint Helena',
        'KN' => 'Saint Kitts and Nevis',
        'LC' => 'Saint Lucia',
        'MF' => 'Saint Martin',
        'PM' => 'Saint Pierre and Miquelon',
        'VC' => 'Saint Vincent and The Grenadines',
        'WS' => 'Samoa',
        'SM' => 'San Marino',
        'ST' => 'Sao Tome and Principe',
        'SA' => 'Saudi Arabia',
        'SN' => 'Senegal',
        'RS' => 'Serbia',
        'SC' => 'Seychelles',
        'SL' => 'Sierra Leone',
        'SG' => 'Singapore',
        'SK' => 'Slovakia (Slovak Republic)',
        'SI' => 'Slovenia',
        'SB' => 'Solomon Islands',
        'SO' => 'Somalia, Somali Republic',
        'ZA' => 'South Africa',
        'GS' => 'South Georgia and The South Sandwich Islands',
        'ES' => 'Spain',
        'LK' => 'Sri Lanka',
        'SD' => 'Sudan',
        'SR' => 'Suriname',
        'SJ' => 'Svalbard & Jan Mayen Islands',
        'SZ' => 'Swaziland',
        'SE' => 'Sweden',
        'CH' => 'Switzerland, Swiss Confederation',
        'SY' => 'Syrian Arab Republic',
        'TW' => 'Taiwan',
        'TJ' => 'Tajikistan',
        'TZ' => 'Tanzania',
        'TH' => 'Thailand',
        'TL' => 'Timor-Leste',
        'TG' => 'Togo',
        'TK' => 'Tokelau',
        'TO' => 'Tonga',
        'TT' => 'Trinidad and Tobago',
        'TN' => 'Tunisia',
        'TR' => 'Turkey',
        'TM' => 'Turkmenistan',
        'TC' => 'Turks and Caicos Islands',
        'TV' => 'Tuvalu',
        'UG' => 'Uganda',
        'UA' => 'Ukraine',
        'AE' => 'United Arab Emirates',
        'GB' => 'United Kingdom',
        'US' => 'United States of America',
        'UM' => 'United States Minor Outlying Islands',
        'VI' => 'United States Virgin Islands',
        'UY' => 'Uruguay, Eastern Republic of',
        'UZ' => 'Uzbekistan',
        'VU' => 'Vanuatu',
        'VE' => 'Venezuela',
        'VN' => 'Vietnam',
        'WF' => 'Wallis and Futuna',
        'EH' => 'Western Sahara',
        'YE' => 'Yemen',
        'ZM' => 'Zambia',
        'ZW' => 'Zimbabwe'
    ];
}

function getImgurThumbnail(string $uri)
{
    $matches = [];
    preg_match('/https?:\/\/i.imgur.com\/([a-zA-Z0-9]{7})(.*)/', $uri, $matches);

    if (!empty($matches)) {
        return 'https://i.imgur.com/' . $matches[1] . 'g' . $matches[2];
    }
}

function getPresences()
{
    return [
        1 => __('presence.online'),
        2 => __('presence.away'),
        3 => __('presence.dnd'),
        4 => __('presence.xa'),
        5 => __('presence.offline'),
        6 => __('presence.error')
    ];
}

function getPresencesTxt()
{
    return [
        1 => 'online',
        2 => 'away',
        3 => 'dnd',
        4 => 'xa',
        5 => 'offline',
        6 => 'server_error'
    ];
}

/**
 * Map the XMPP form vars to Material Symbols
 */
function varToIcons(string $var)
{
    $icons = [
        // Pubsub
        'pubsub#deliver_payloads' => 'add_box',
        'pubsub#deliver_notifications' => 'notifications',
        'pubsub#notify_config' => 'notifications',
        'pubsub#notify_delete' => 'delete',
        'pubsub#notify_retract' => 'delete_sweep',
        'pubsub#persist_items' => 'save',
        'pubsub#presence_based_delivery' => 'notifications_active',
        'pubsub#purge_offline' => 'delete_forever',
        'pubsub#subscribe' => 'how_to_reg',
        'pubsub#type' => 'space_dashboard',

        // Muc
        'muc#roomconfig_persistentroom' => 'save',
        'muc#roomconfig_publicroom' => 'wifi_tethering',
        'muc#roomconfig_passwordprotectedroom' => 'lock',
        'muc#roomconfig_membersonly' => 'playlist_add_check',
        'muc#roomconfig_moderatedroom' => 'stars',
        'muc#roomconfig_changesubject' => 'title',
        'muc#roomconfig_allowinvites' => 'mail',
        'allow_private_messages' => 'message',
        'allow_query_users' => 'portrait',
        'allow_visitor_nickchange' => '3p',
        'allow_visitor_status' => 'description',
        'allow_voice_requests' => 'voice_selection',
        'allow_subscription' => 'how_to_reg',
        'enable_hats' => 'badge',
        'mam' => 'archive',
        'members_by_default' => 'remember_me',
        'public_list' => 'public',
    ];

    if (array_key_exists($var, $icons)) {
        return $icons[$var];
    }

    return 'tune';
}

/**
 * Generate a standard UUID
 */
function generateUUID($string = false)
{
    $data = ($string != false) ? $string : openssl_random_pseudo_bytes(16);

    $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0010
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10

    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

/**
 * @desc Generate a simple random key
 * @params The size of the key
 */
function generateKey(?int $size = 16, bool $withCapitals = true): string
{
    $hashChars = 'abcdefghijklmnopqrstuvwxyz';
    if ($withCapitals) $hashChars .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

    $hash = '';

    for ($i = 0; $i < $size; $i++) {
        $hash .= $hashChars[random_int(0, strlen($hashChars) - 1)];
    }

    return $hash;
}

define('DEFAULT_HTTP_USER_AGENT', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:73.0) Gecko/20100101 Firefox/73.0');

/**
 * @desc Request a url async
 */
function requestAsyncURL(string $url, int $timeout = 10, array $headers = []): Promise
{
    $browser = new React\Http\Browser;

    return $browser->withTimeout($timeout)->get($url, $headers);
}

/**
 * @desc Request the Resolver Worker
 */
function requestResolverWorker(string $url, int $timeout = 30): PromiseInterface
{
    $connector = new React\Socket\FixedUriConnector(
        'unix://' . RESOLVER_SOCKET,
        new React\Socket\UnixConnector()
    );

    $browser = new React\Http\Browser($connector);
    $data['url'] = $url;

    return $browser
        ->withTimeout($timeout)
        ->post($url, [], json_encode($data))
        ->then(function (Response $response) {
            return json_decode($response->getBody());
        });
}

/**
 * @desc Request the Templater Worker
 */
function requestTemplaterWorker(string $widget, string $method, ?Packet $data = null): PromiseInterface
{
    $connector = new React\Socket\FixedUriConnector(
        'unix://' . TEMPLATER_SOCKET,
        new React\Socket\UnixConnector()
    );

    $browser = new React\Http\Browser($connector);
    $payload = [
        'sid' => SESSION_ID,
        'jid' => me()->id,
        'widget' => $widget,
        'method' => $method,
        'data' => $data
    ];

    return $browser
        ->post('http://templater', [], json_encode($payload))
        ->then(function (Response $response) {
            return json_decode($response->getBody());
        });
}

/*
 * @desc Request a simple url
 */
function requestURL(string $url, int $timeout = 10, bool $json = false, array $headers = []): ?string
{
    if ($json) {
        array_push($headers, 'Accept: application/json');
    }

    $connector = null;

    // Disable SSL if the host requested is the local one
    if (parse_url(config('daemon.url'), PHP_URL_HOST) == parse_url($url, PHP_URL_HOST)) {
        $connector = new React\Socket\Connector([
            'tls' => [
                'verify_peer' => false,
                'verify_peer_name' => false
            ]
        ]);
    }

    $browser = (new React\Http\Browser($connector))
        ->withTimeout($timeout)
        ->withHeader('User-Agent', DEFAULT_HTTP_USER_AGENT)
        ->withFollowRedirects(true);

    try {
        $response = await($browser->get($url, $headers));
        // response successfully received
        return (string)$response->getBody();
    } catch (Exception $e) {
        return null;
    }
}

/**
 * Request the internal API
 */
function requestAPI(string $action, int $timeout = 2, ?array $post = null): string|false
{
    $browser = (new React\Http\Browser(
        new React\Socket\FixedUriConnector(
            API_SOCKET,
            new React\Socket\UnixConnector()
        )
    ))->withTimeout($timeout)
        ->withHeader('Content-Type', 'application/x-www-form-urlencoded')
        ->withHeader('Host', $action);

    $url = 'http:/' . $action;

    try {
        $response = await(
            $post
                ? $browser->post($url, body: http_build_query($post))
                : $browser->get($url)
        );

        return (string)$response->getBody();
    } catch (Exception $e) {
        return false;
    }
}

/**
 * @desc Get distance between two coordinates
 *
 * @param float $latitudeFrom
 * @param float $longitudeFrom
 * @param float $latitudeTo
 * @param float $longitudeTo
 *
 * @return float [km]
 */
function getDistance(float $latitudeFrom, float $longitudeFrom, float $latitudeTo, float $longitudeTo): float
{
    $rad = M_PI / 180;

    $theta = $longitudeFrom - $longitudeTo;
    $dist = sin($latitudeFrom * $rad)
        * sin($latitudeTo * $rad) +  cos($latitudeFrom * $rad)
        * cos($latitudeTo * $rad) * cos($theta * $rad);

    return acos($dist) / $rad * 60 *  1.853;
}

/*
 * @desc Get the URI of a smiley
 */
function getSmileyPath($id)
{
    return BASE_URI . 'theme/img/emojis/svg/' . $id . '.svg';
}

/**
 * @desc Return the url of an avatar placeholder
 */
function avatarPlaceholder(string $id): string
{
    return \Movim\Route::urlize('picture', null, ['type' => 'avatar', 'id' => urlencode($id)]);
}

/*
 * @desc Protect a picture URL by using the internal Proxy
 */
function protectPicture($url)
{
    $emptyPicture =  \Movim\Route::urlize('picture');
    $emptyPicture = preg_replace("(^//)", 'https://', $emptyPicture);

    // The picture is already protected
    if (substr($url, 0, strlen($emptyPicture)) === $emptyPicture) {
        return $url;
    }

    return \Movim\Route::urlize('picture', null, ['type' => 'picture', 'url' => urlencode($url)]);
}

/*
 * @desc Translate something
 */
function __()
{
    $args = func_get_args();
    $l = Movim\i18n\Locale::start();

    $string = array_shift($args);
    return $l->translate($string, $args);
}

/*
 * @desc Get the browser name from a user agent
 */
function getBrowser(string $userAgent): ?string
{
    $t = strtolower($userAgent);
    $t = ' ' . $t;

    if (strpos($t, 'opera')) return 'Opera';
    elseif (strpos($t, 'edge')) return 'Edge';
    elseif (strpos($t, 'chrome')) return 'Chrome';
    elseif (strpos($t, 'safari')) return 'Safari';
    elseif (strpos($t, 'firefox')) return 'Firefox';
}

/*
 * @desc Get the platform from the user agent
 */
function getPlatform(string $userAgent): ?string
{
    $oses =  [
        '/windows nt 10/i'      =>  'Windows 10',
        '/windows nt 6.3/i'     =>  'Windows 8.1',
        '/windows nt 6.2/i'     =>  'Windows 8',
        '/windows nt 6.1/i'     =>  'Windows 7',
        '/windows nt 6.0/i'     =>  'Windows Vista',
        '/macintosh|mac os x/i' =>  'Mac OS X',
        '/mac_powerpc/i'        =>  'Mac OS 9',
        '/linux/i'              =>  'Linux',
        '/ubuntu/i'             =>  'Ubuntu',
        '/iphone/i'             =>  'iPhone',
        '/ipod/i'               =>  'iPod',
        '/ipad/i'               =>  'iPad',
        '/android/i'            =>  'Android',
        '/blackberry/i'         =>  'BlackBerry',
        '/webos/i'              =>  'Mobile'
    ];

    foreach ($oses as $regex => $value) {
        if (preg_match($regex, $userAgent)) {
            return $value;
        }
    }
}

/**
 * @desc Get PHP hash to IANA hashes conversion
 * https://www.iana.org/assignments/hash-function-text-names/hash-function-text-names.xhtml
 * https://www.php.net/manual/en/function.hash-algos.php
 */
function phpToIANAHash(): array
{
    return [
        'md2' => 'md2',
        'md5' => 'md5',
        'sha1' => 'sha1',
        'sha224' => 'sha-224',
        'sha256' => 'sha-256',
        'sha384' => 'sha-384',
        'sha512' => 'sha-512',
    ];
}

function IANAHashToPhp(): array
{
    return [
        'md2' => 'md2',
        'md5' => 'md5',
        'sha1' => 'sha1', // https://xmpp.org/extensions/xep-0231.html#algo
        'sha-224' => 'sha224',
        'sha256' => 'sha256', // retro-compatibility
        'sha-256' => 'sha256',
        'sha-384' => 'sha384',
        'sha-512' => 'sha512',
    ];
}

/**
 * @desc Get OMEMO fingerprint from base64
 */
function base64ToFingerPrint(string $base64): string
{
    $buffer = base64_decode($base64);
    $hex = unpack('H*', $buffer);
    return implode(' ', str_split(substr($hex[1], 2), 8));
}
