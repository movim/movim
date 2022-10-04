<?php

use Monolog\Logger;
use Monolog\Handler\SyslogHandler;
use Monolog\Handler\StreamHandler;
use Movim\Image;

class Utils
{
    public static function info($message)
    {
        if (LOG_LEVEL != null && LOG_LEVEL > 0 && getenv('debug')) {
            $log = new Logger('movim');

            $handler = new SyslogHandler('movim');

            if (LOG_LEVEL > 1) {
                $log->pushHandler(new StreamHandler(LOG_PATH . '/info.log'));
            }

            $log->pushHandler($handler);

            $errlines = explode("\n", $message);
            foreach ($errlines as $txt) {
                $log->info($txt);
            }
        }
    }

    /**
     * Log a string, only used for debug purposes
     */
    public static function debug($logs)
    {
        $log = new Logger('movim');
        $log->pushHandler(new StreamHandler(LOG_PATH . '/debug.log'));
        if (is_array($logs)) {
            $log->debug('', $logs);
        } else {
            $log->debug($logs);
        }
    }

    /**
     * Log a string, only used for debug purposes
     */
    public static function error($logs)
    {
        $log = new Logger('movim');
        $log->pushHandler(new SyslogHandler('movim'));

        if (defined('LOG_LEVEL') && LOG_LEVEL > 1) {
            $log->pushHandler(new StreamHandler(LOG_PATH . '/errors.log'));
        }

        $log->error($logs);
    }
}

/**
 * Check if the session exists
 */
function isLogged()
{
    return (bool)(\Movim\Session::start())->get('jid');
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
        'handheld'      => __('client.phone'),
        'web'           => __('client.web'),
        'registered'    => __('client.registered')
    ];
}

/**
 * Check if Posts collection is gallery
 */
function isPostGallery($postCollection): bool
{
    // For now we detect if a node is a gallery if all the publications have an attached picture
    // and if the post contents are short.
    $shortCount = 0;

    $gallery = $postCollection->every(function ($post) use (&$shortCount) {
        if ($post->isShort()) $shortCount++;
        return $post->picture != null;
    });

    if ($gallery && $shortCount < $postCollection->count()/2) $gallery = false;

    return $gallery;
}

/**
 * Resolve infos from a Posts collection
 */
function resolveInfos($postCollection)
{
    $serverNodes = $postCollection->map(function($item) {
        return ['server' => $item->server, 'node' => $item->node];
    })->unique(function ($item) {
        return $item['server'].$item['node'];
    });

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

        $infos = $infos->get()->keyBy(function ($item) {
            return $item['server'].$item['node'];
        });

        $postCollection->map(function($item) use ($infos) {
            $item->info = $infos->get($item->server.$item->node);
            return $item;
        });

        return $postCollection;
    }
}

/**
 * Return a picture with a specific size
 */
function getPhoto(string $key, string $size = 'm')
{
    $sizes = [
        'xxl'   => [1280, 300],
        'xl'    => [512, false],
        'l'     => [210, false],
        'm'     => [120, false],
        's'     => [50, false],
        'o'     => [false, false]
    ];

    return Image::getOrCreate($key, $sizes[$size][0], $sizes[$size][1]);
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

function getMood()
{
    return [
        'afraid'        => __('mood.afraid'), // Impressed with fear or apprehension; in fear; apprehensive.
        'amazed'        => __('mood.amazed'), // Astonished; confounded with fear, surprise or wonder.
        'amorous'       => __('mood.amorous'), // Inclined to love; having a propensity to love, or to sexual enjoyment; loving, fond, affectionate, passionate, lustful, sexual, etc.
        'angry'         => __('mood.angry'), // Displaying or feeling anger, i.e., a strong feeling of displeasure, hostility or antagonism towards someone or something, usually combined with an urge to harm.
        'annoyed'       => __('mood.annoyed'), // To be disturbed or irritated, especially by continued or repeated acts.
        'anxious'       => __('mood.anxious'), // Full of anxiety or disquietude; greatly concerned or solicitous, esp. respecting something future or unknown; being in painful suspense.
        'aroused'       => __('mood.aroused'), // To be stimulated in one's feelings, especially to be sexually stimulated.
        'ashamed'       => __('mood.ashamed'), // Feeling shame or guilt.
        'bored'         => __('mood.bored'), // Suffering from boredom; uninterested, without attention.
        'brave'         => __('mood.brave'), // Strong in the face of fear; courageous.
        'calm'          => __('mood.calm'), // Peaceful, quiet.
        'cautious'      => __('mood.cautious'), // Taking care or caution; tentative.
        'cold'          => __('mood.cold'), // Feeling the sensation of coldness, especially to the point of discomfort.
        'confident'     => __('mood.confident'), // Feeling very sure of or positive about something, especially about one's own capabilities.
        'confused'      => __('mood.confused'), // Chaotic, jumbled or muddled.
        'contemplative' => __('mood.contemplative'), // Feeling introspective or thoughtful.
        'contented'     => __('mood.contented'), // Pleased at the satisfaction of a want or desire; satisfied.
        'cranky'        => __('mood.cranky'), // Grouchy, irritable; easily upset.
        'crazy'         => __('mood.crazy'), // Feeling out of control; feeling overly excited or enthusiastic.
        'creative'      => __('mood.creative'), // Feeling original, expressive, or imaginative.
        'curious'       => __('mood.curious'), // Inquisitive; tending to ask questions, investigate, or explore.
        'dejected'      => __('mood.dejected'), // Feeling sad and dispirited.
        'depressed'     => __('mood.depressed'), // Severely despondent and unhappy.
        'disappointed'  => __('mood.disappointed'), // Defeated of expectation or hope; let down.
        'disgusted'     => __('mood.disgusted'), // Filled with disgust; irritated and out of patience.
        'dismayed'      => __('mood.dismayed'), // Feeling a sudden or complete loss of courage in the face of trouble or danger.
        'distracted'    => __('mood.distracted'), // Having one's attention diverted; preoccupied.
        'embarrassed'   => __('mood.embarrassed'), // Having a feeling of shameful discomfort.
        'envious'       => __('mood.envious'), // Feeling pain by the excellence or good fortune of another.
        'excited'       => __('mood.excited'), // Having great enthusiasm.
        'flirtatious'   => __('mood.flirtatious'), // In the mood for flirting.
        'frustrated'    => __('mood.frustrated'), // Suffering from frustration; dissatisfied, agitated, or discontented because one is unable to perform an action or fulfill a desire.
        'grateful'      => __('mood.grateful'), // Feeling appreciation or thanks.
        'grieving'      => __('mood.grieving'), // Feeling very sad about something, especially something lost; mournful; sorrowful.
        'grumpy'        => __('mood.grumpy'), // Unhappy and irritable.
        'guilty'        => __('mood.guilty'), // Feeling responsible for wrongdoing; feeling blameworthy.
        'happy'         => __('mood.happy'), // Experiencing the effect of favourable fortune; having the feeling arising from the consciousness of well-being or of enjoyment; enjoying good of any kind, as peace, tranquillity, comfort; contented; joyous.
        'hopeful'       => __('mood.hopeful'), // Having a positive feeling, belief, or expectation that something wished for can or will happen.
        'hot'           => __('mood.hot'), // Feeling the sensation of heat, especially to the point of discomfort.
        'humbled'       => __('mood.humbled'), // Having or showing a modest or low estimate of one's own importance; feeling lowered in dignity or importance.
        'humiliated'    => __('mood.humiliated'), // Feeling deprived of dignity or self-respect.
        'hungry'        => __('mood.hungry'), // Having a physical need for food.
        'hurt'          => __('mood.hurt'), // Wounded, injured, or pained, whether physically or emotionally.
        'impressed'     => __('mood.impressed'), // Favourably affected by something or someone.
        'in_awe'        => __('mood.in_awe'), // Feeling amazement at something or someone; or feeling a combination of fear and reverence.
        'in_love'       => __('mood.in_love'), // Feeling strong affection, care, liking, or attraction..
        'indignant'     => __('mood.indignant'), // Showing anger or indignation, especially at something unjust or wrong.
        'interested'    => __('mood.interested'), // Showing great attention to something or someone; having or showing interest.
        'intoxicated'   => __('mood.intoxicated'), // Under the influence of alcohol; drunk.
        'invincible'    => __('mood.invincible'), // Feeling as if one cannot be defeated, overcome or denied.
        'jealous'       => __('mood.jealous'), // Fearful of being replaced in position or affection.
        'lonely'        => __('mood.lonely'), // Feeling isolated, empty, or abandoned.
        'lost'          => __('mood.lost'), // Unable to find one's way, either physically or emotionally.
        'lucky'         => __('mood.lucky'), // Feeling as if one will be favored by luck.
        'mean'          => __('mood.mean'), // Causing or intending to cause intentional harm; bearing ill will towards another; cruel; malicious.
        'moody'         => __('mood.moody'), // Given to sudden or frequent changes of mind or feeling; temperamental.
        'nervous'       => __('mood.nervous'), // Easily agitated or alarmed; apprehensive or anxious.
        'neutral'       => __('mood.neutral'), // Not having a strong mood or emotional state.
        'offended'      => __('mood.offended'), // Feeling emotionally hurt, displeased, or insulted.
        'outraged'      => __('mood.outraged'), // Feeling resentful anger caused by an extremely violent or vicious attack, or by an offensive, immoral, or indecent act.
        'playful'       => __('mood.playful'), // Interested in play; fun, recreational, unserious, lighthearted; joking, silly.
        'proud'         => __('mood.proud'), // Feeling a sense of one's own worth or accomplishment.
        'relaxed'       => __('mood.relaxed'), // Having an easy-going mood; not stressed; calm.
        'relieved'      => __('mood.relieved'), // Feeling uplifted because of the removal of stress or discomfort.
        'remorseful'    => __('mood.remorseful'), // Feeling regret or sadness for doing something wrong.
        'restless'      => __('mood.restless'), // Without rest; unable to be still or quiet; uneasy; continually moving.
        'sad'           => __('mood.sad'), // Feeling sorrow; sorrowful, mournful.
        'sarcastic'     => __('mood.sarcastic'), // Mocking and ironical.
        'satisfied'     => __('mood.satisfied'), // Pleased at the fulfillment of a need or desire.
        'serious'       => __('mood.serious'), // Without humor or expression of happiness; grave in manner or disposition; earnest; thoughtful; solemn.
        'shocked'       => __('mood.shocked'), // Surprised, startled, confused, or taken aback.
        'shy'           => __('mood.shy'), // Feeling easily frightened or scared; timid; reserved or coy.
        'sick'          => __('mood.sick'), // Feeling in poor health; ill.
        'sleepy'        => __('mood.sleepy'), // Feeling the need for sleep.
        'spontaneous'   => __('mood.spontaneous'), // Acting without planning; natural; impulsive.
        'stressed'      => __('mood.stressed'), // Suffering emotional pressure.
        'strong'        => __('mood.strong'), // Capable of producing great physical force; or, emotionally forceful, able, determined, unyielding.
        'surprised'     => __('mood.surprised'), // Experiencing a feeling caused by something unexpected.
        'thankful'      => __('mood.thankful'), // Showing appreciation or gratitude.
        'thirsty'       => __('mood.thirsty'), // Feeling the need to drink.
        'tired'         => __('mood.tired'), // In need of rest or sleep.
        'undefined'     => __('mood.undefined'), // [Feeling any emotion not defined here.]
        'weak'          => __('mood.weak'), // Lacking in force or ability, either physical or emotional.
        'worried'       => __('mood.worried') // Thinking about unpleasant things that have happened or that might happen; feeling afraid and unhappy.
    ];
}

/**
 * Map the XMPP form vars to Material icons
 */
function varToIcons(string $var)
{
    $icons = [
        // Pubsub
        'pubsub#deliver_payloads' => 'add_box',
        'pubsub#notify_config' => 'notifications',
        'pubsub#notify_delete' => 'delete',
        'pubsub#notify_retract' => 'delete_sweep',
        'pubsub#persist_items' => 'save',
        'pubsub#deliver_notifications' => 'notifications_active',

        // Muc
        'muc#roomconfig_persistentroom' => 'save',
        'muc#roomconfig_publicroom' => 'wifi_tethering',
        'muc#roomconfig_passwordprotectedroom' => 'lock',
        'muc#roomconfig_membersonly' => 'playlist_add_check',
        'muc#roomconfig_moderatedroom' => 'stars',
        'muc#roomconfig_changesubject' => 'title',
        'muc#roomconfig_allowinvites' => 'mail',
        'allow_visitor_status' => 'description',
        'allow_private_messages' => 'message',
        'allow_query_users' => 'portrait',
        'mam' => 'archive',
    ];

    if (array_key_exists($var, $icons)) {
        return $icons[$var];
    }

    return 'noise_control_off';
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
function generateKey($size)
{
    // Generating the session cookie's hash.
    $hash_chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $hash = "";

    for ($i = 0; $i < $size; $i++) {
        $r = mt_rand(0, strlen($hash_chars) - 1);
        $hash .= $hash_chars[$r];
    }

    return $hash;
}

//define('DEFAULT_HTTP_USER_AGENT', 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://google.com/bot.html)');
define('DEFAULT_HTTP_USER_AGENT', 'Mozilla/5.0 (Android 4.4; Mobile; rv:41.0) Gecko/41.0 Firefox/41.0');

/**
 * @desc Request a url async
 */
function requestAsyncURL(string $url, int $timeout = 10, array $headers = [])
{
    $browser = new React\Http\Browser;

    return $browser->withTimeout($timeout)->get($url, $headers);
}

/*
 * @desc Request a simple url
 */
function requestURL(string $url, int $timeout = 10, $post = false, bool $json = false, array $headers = [])
{
    if ($json) {
        array_push($headers, 'Accept: application/json');
    }

    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    //curl_setopt($ch, CURLOPT_ENCODING, 'gzip,deflate');
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_USERAGENT, DEFAULT_HTTP_USER_AGENT);

    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    if ($post) {
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    }

    $content = curl_exec($ch);
    return curl_errno($ch) == 0 ? $content : false;
}

/*
 * Request the headers of a URL
 */
function requestHeaders(string $url, $timeout = 2)
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    //curl_setopt($ch, CURLOPT_ENCODING, 'gzip,deflate');
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_HEADER, 1);
    curl_setopt($ch, CURLOPT_NOBODY, 1);
    curl_setopt($ch, CURLOPT_USERAGENT, DEFAULT_HTTP_USER_AGENT);

    curl_exec($ch);

    return curl_getinfo($ch);
}

/**
 * Request the internal API
 */
function requestAPI(string $action, int $timeout = 2, $post = false)
{
    $ch = curl_init('http:/' . $action);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_UNIX_SOCKET_PATH, API_SOCKET);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    if (is_array($post)) {
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    }

    $content = curl_exec($ch);
    return curl_errno($ch) == 0 ? $content : false;
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

/*
 * @desc Protect a picture URL by using the internal Proxy
 */
function protectPicture($url)
{
    $emptyPicture =  \Movim\Route::urlize('picture', '');
    $emptyPicture = preg_replace("(^//)", 'https://', $emptyPicture);

    // The picture is already protected
    if (substr($url, 0, strlen($emptyPicture)) === $emptyPicture) {
        return $url;
    }

    return \Movim\Route::urlize('picture', urlencode($url));
}

/*
 * @desc Copy the stickers in the public cache
 */
function compileStickers(): int
{
    $count = 0;

    foreach (glob(PUBLIC_PATH . '/stickers/*/*.png', GLOB_NOSORT) as $path) {
        $key = basename($path, '.png');

        if ($key != 'icon') {
            $count++;
            copy($path, PUBLIC_CACHE_PATH.hash(Image::$hash, $key).'_o.png');
        }
    }

    return $count;
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

    if     (strpos($t, 'opera'  )) return 'Opera';
    elseif (strpos($t, 'edge'   )) return 'Edge';
    elseif (strpos($t, 'chrome' )) return 'Chrome';
    elseif (strpos($t, 'safari' )) return 'Safari';
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