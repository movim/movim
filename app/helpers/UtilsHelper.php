<?php

use Monolog\Logger;
use Monolog\Handler\SyslogHandler;
use Monolog\Handler\StreamHandler;

class Utils {
    public static function log($message, $priority = '')
    {
        if(LOG_LEVEL != null && LOG_LEVEL > 0) {
            $log = new Logger('movim');

            $handler = new SyslogHandler('movim');

            if(LOG_LEVEL > 1)
                $log->pushHandler(new StreamHandler(LOG_PATH.'/movim.log', Logger::DEBUG));

            $log->pushHandler($handler, Logger::DEBUG);

            $errlines = explode("\n",$message);
            foreach ($errlines as $txt) { $log->addDebug($txt); }
        }
    }
}

/**
 * Return the list of gender
 */
function getGender() {
    return array('N' => __('gender.nil'),
                 'M' => __('gender.male'),
                 'F' => __('gender.female'),
                 'O' => __('gender.other')
                );
}

/**
 * Return the list of client types
 */
function getClientTypes() {
    return array(
                'bot'           => __('client.bot'),
                'pc'            => __('client.desktop'),
                'phone'         => __('client.phone'),
                'handheld'      => __('client.phone'),
                'web'           => __('client.web'),
                'registered'    => __('client.registered')
                );
}

/**
 * Return a XEP to namespace association
 */
function getXepNamespace() {
    return array(
            '0004' => array('name' => 'Data Forms',             'category' => 'client',     'ns' => 'jabber:x:data'),
            '0012' => array('name' => 'Last Activity',          'category' => 'chat',       'ns' => 'jabber:iq:last'),
            '0030' => array('name' => 'Service Discovery',      'category' => 'client',     'ns' => 'http://jabber.org/protocol/disco#info'),
            '0045' => array('name' => 'Multi-User Chat',        'category' => 'chat',       'ns' => 'http://jabber.org/protocol/muc'),
            '0050' => array('name' => 'Ad-Hoc Commands',        'category' => 'client',     'ns' => 'http://jabber.org/protocol/commands'),
            '0054' => array('name' => 'vcard-temp',             'category' => 'client',     'ns' => 'vcard-temp'),
            '0071' => array('name' => 'XHTML-IM',               'category' => 'chat',       'ns' => 'http://jabber.org/protocol/xhtml-im'),
            '0080' => array('name' => 'User Location',          'category' => 'profile',    'ns' => 'http://jabber.org/protocol/geoloc'),
            '0084' => array('name' => 'User Avatar',            'category' => 'profile',    'ns' => 'urn:xmpp:avatar:data'),
            '0085' => array('name' => 'Chat State Notifications', 'category' => 'chat',     'ns' => 'http://jabber.org/protocol/chatstates'),
            '0092' => array('name' => 'Software Version',       'category' => 'client',     'ns' => 'jabber:iq:version'),
            '0107' => array('name' => 'User Mood',              'category' => 'profile',    'ns' => 'http://jabber.org/protocol/mood'),
            '0108' => array('name' => 'User Activity',          'category' => 'profile',    'ns' => 'http://jabber.org/protocol/activity'),
            '0115' => array('name' => 'Entity Capabilities',    'category' => 'client',     'ns' => 'http://jabber.org/protocol/caps'),
            '0118' => array('name' => 'User Tune',              'category' => 'profile',    'ns' => 'http://jabber.org/protocol/tune'),
            '0124' => array('name' => 'Bidirectional-streams Over Synchronous HTTP (BOSH)', 'category' => 'client',    'ns' => 'http://jabber.org/protocol/httpbind'),
            '0152' => array('name' => 'Reachability Addresses', 'category' => 'client',     'ns' => 'urn:xmpp:reach:0'),
            '0166' => array('name' => 'Jingle',                 'category' => 'jingle',     'ns' => 'urn:xmpp:jingle:1'),
            '0167' => array('name' => 'Jingle RTP Sessions',    'category' => 'jingle',     'ns' => 'urn:xmpp:jingle:apps:rtp:1'),
            '0172' => array('name' => 'User Nickname',          'category' => 'profile',    'ns' => 'http://jabber.org/protocol/nick'),
            '0176' => array('name' => 'Jingle ICE-UDP Transport Method', 'category' => 'jingle',     'ns' => 'urn:xmpp:jingle:transports:ice-udp:1'),
            '0177' => array('name' => 'Jingle Raw UDP Transport Method', 'category' => 'jingle',     'ns' => 'urn:xmpp:jingle:transports:raw-udp:1'),
            '0184' => array('name' => 'Message Delivery Receipts', 'category' => 'chat',    'ns' => 'urn:xmpp:receipts'),
            '0186' => array('name' => 'Invisible Command',      'category' => 'chat',       'ns' => 'urn:xmpp:invisible:0'),
            '0199' => array('name' => 'XMPP Ping',              'category' => 'client',     'ns' => 'urn:xmpp:ping'),
            '0202' => array('name' => 'Entity Time',            'category' => 'client',     'ns' => 'urn:xmpp:time'),
            '0224' => array('name' => 'Attention',              'category' => 'chat',       'ns' => 'urn:xmpp:attention:0'),
            '0231' => array('name' => 'Bits of Binary',         'category' => 'chat',       'ns' => 'urn:xmpp:bob'),
            '0234' => array('name' => 'Jingle File Transfer',   'category' => 'jingle',     'ns' => 'urn:xmpp:jingle:apps:file-transfer:4'),
            '0249' => array('name' => 'Direct MUC Invitations', 'category' => 'chat',       'ns' => 'jabber:x:conference'),
            '0277' => array('name' => 'Microblogging over XMPP','category' => 'social',     'ns' => 'urn:xmpp:microblog:0'),
            '0280' => array('name' => 'Message Carbons',        'category' => 'chat',       'ns' => 'urn:xmpp:carbons:2'),
            '0292' => array('name' => 'vCard4 Over XMPP',       'category' => 'profile',    'ns' => 'urn:xmpp:vcard4'),
            '0301' => array('name' => 'In-Band Real Time Text', 'category' => 'chat',       'ns' => 'urn:xmpp:rtt:0'),
            '0308' => array('name' => 'Last Message Correction', 'category' => 'chat',       'ns' => 'urn:xmpp:message-correct:0'),
            '0313' => array('name' => 'Message Archive Management', 'category' => 'chat',       'ns' => 'urn:xmpp:mam:0'),
            '0320' => array('name' => 'Use of DTLS-SRTP in Jingle Sessions', 'category' => 'jingle',     'ns' => 'urn:xmpp:jingle:apps:dtls:0'),
            '0323' => array('name' => 'Internet of Things - Sensor Data', 'category' => 'iot',        'ns' => 'urn:xmpp:iot:sensordata'),
            '0324' => array('name' => 'Internet of Things - Provisioning', 'category' => 'iot',        'ns' => 'urn:xmpp:iot:provisioning'),
            '0325' => array('name' => 'Internet of Things - Control', 'category' => 'iot',        'ns' => 'urn:xmpp:iot:control'),
            '0326' => array('name' => 'Internet of Things - Concentrators', 'category' => 'iot',        'ns' => 'urn:xmpp:iot:concentrators'),
            '0327' => array('name' => 'Rayo', 'category' => 'rayo',       'ns' => 'urn:xmpp:rayo:0'),
            '0330' => array('name' => 'Pubsub Subscription',    'category' => 'social',     'ns' => 'urn:xmpp:pubsub:subscription'),
            '0332' => array('name' => 'HTTP over XMPP transport', 'category' => 'client',   'ns' => 'urn:xmpp:http'),
            '0337' => array('name' => 'Event Logging over XMPP', 'category' => 'client',    'ns' => 'urn:xmpp:eventlog'),
            '0338' => array('name' => 'Jingle Grouping Framework', 'category' => 'jingle',  'ns' => 'urn:ietf:rfc:5888'),
            '0339' => array('name' => 'Source-Specific Media Attributes in Jingle', 'category' => 'jingle',     'ns' => 'urn:ietf:rfc:5576'),
            '0340' => array('name' => 'COnferences with LIghtweight BRIdging (COLIBRI)', 'category' => 'jingle',     'ns' => 'http://jitsi.org/protocol/colibri'),
            '0341' => array('name' => 'Rayo CPA', 'category' => 'rayo',       'ns' => 'urn:xmpp:rayo:cpa:0'),
            '0342' => array('name' => 'Rayo Fax', 'category' => 'rayo',       'ns' => 'urn:xmpp:rayo:fax:1'),
            '0347' => array('name' => 'Internet of Things - Discovery', 'category' => 'iot',        'ns' => 'urn:xmpp:iot:discovery'),
            '0348' => array('name' => 'Signing Forms', 'category' => 'client',     'ns' => 'urn:xmpp:xdata:signature:oauth1'),
            );
}

/**
 * Return a list of all the country
 */
function getCountries() {
    return array(
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
    );
}

/**
 * Return the list of marital status
 */
function getMarital() {
    return array(
            'none'          => __('marital.nil'),
            'single'        => __('marital.single'),
            'relationship'  => __('marital.relationship'),
            'married'       => __('marital.married'),
            'divorced'      => __('marital.divorced'),
            'widowed'       => __('marital.widowed'),
            'cohabiting'    => __('marital.cohabiting'),
            'union'         => __('marital.union')
        );
}

function getPresences() {
    return array(
            1 => __('presence.online'),
            2 => __('presence.away'),
            3 => __('presence.dnd'),
            4 => __('presence.xa'),
            5 => __('presence.offline'),
            6 => __('presence.error')
        );

}

function getPresencesTxt() {
    return array(
                1 => 'online',
                2 => 'away',
                3 => 'dnd',
                4 => 'xa',
                5 => 'offline',
                6 => 'server_error'
            );
}

function getMood() {
    return array(
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
    );
}

/*
 * Generate a standard UUID
 */
function generateUUID($string = false) {
    if($string != false)
        $data = $string;
    else
        $data = openssl_random_pseudo_bytes(16);

    $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0010
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10

    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

function movim_log($logs) {
    $log = new Logger('movim');
    $log->pushHandler(new SyslogHandler('movim'));

    $log->pushHandler(new StreamHandler(LOG_PATH.'/logger.log', Logger::DEBUG));
    if(is_array($logs))
        $log->addInfo('', $logs);
    else
        $log->addInfo($logs);
}

/*
 * @desc Generate a simple random key
 * @params The size of the key
 */
function generateKey($size) {
    // Generating the session cookie's hash.
    $hash_chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $hash = "";

    for($i = 0; $i < $size; $i++) {
        $r = mt_rand(0, strlen($hash_chars) - 1);
        $hash.= $hash_chars[$r];
    }
    return $hash;
}

/*
 * @desc Get the range aroung a position with a radius
 */
function geoRadius($latitude, $longitude, $radius) {
    $lat_range = $range/69.172;
    $lon_range = abs($range/(cos($latitude) * 69.172));
    $min_lat = number_format($latitude - $lat_range, "4", ".", "");
    $max_lat = number_format($latitude + $lat_range, "4", ".", "");
    $min_lon = number_format($longitude - $lon_range, "4", ".", "");
    $max_lon = number_format($longitude + $lon_range, "4", ".", "");

    return array($min_lat, $max_lat, $min_lon, $max_lon);
}

/*
 * @desc Request a simple url
 */
function requestURL($url, $timeout = 10, $post = false) {
    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_ENCODING, 'gzip,deflate');
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:31.0) Gecko/20100101 Firefox/31.0');

    if(is_array($post)) {
        $params = '';

        foreach($post as $key => $value) {
            $params .= $key . '=' . $value .'&';
        }
        curl_setopt ($ch, CURLOPT_POST, 1);
        curl_setopt ($ch, CURLOPT_POSTFIELDS, $params);
    }

    $rs = [];

    $content = curl_exec($ch);

    $rs['content'] = $content;
    $rs['errno'] = curl_errno($ch);
    $rs['errmsg'] = curl_error($ch);
    $rs['header'] = curl_getinfo($ch);

    if($rs['errno'] == 0) {
        return $rs['content'];
    } else {
        return false;
    }
}

/*
 * @desc Get the URI of a smiley
 */
function getSmileyPath($id)
{
    return BASE_URI.'/themes/material/img/emojis/svg/'.$id.'.svg';
}

/*
 * @desc Translate something
 */
function __() {
    $args = func_get_args();
    $l = Movim\i18n\Locale::start();

    $string = array_shift($args);
    return $l->translate($string, $args);
}

function createEmailPic($jid, $email) {
    $cachefile = DOCUMENT_ROOT.'/cache/'.$jid.'_email.png';

    if(file_exists(DOCUMENT_ROOT.'/cache/'.$jid.'_email.png'))
        unlink(DOCUMENT_ROOT.'/cache/'.$jid.'_email.png');

    $draw = new ImagickDraw();
    try {
        $draw->setFontSize(13);
        $draw->setGravity(Imagick::GRAVITY_CENTER);

        $canvas = new Imagick();

        $metrics = $canvas->queryFontMetrics($draw, $email);

        $canvas->newImage($metrics['textWidth'], $metrics['textHeight'], "transparent", "png");
        $canvas->annotateImage($draw, 0, 0, 0, $email);

        $canvas->setImageFormat('PNG');
        $canvas->writeImage($cachefile);

        $canvas->clear();
    } catch (ImagickException $e) {
        error_log($e->getMessage());
    }
}

