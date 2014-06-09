<?php

/**
 * @file Utils.php
 * This file is part of PROJECT.
 * 
 * @brief Description
 *
 * @author Etenil <etenil@etenilsrealm.nl>
 *
 * @version 1.0
 * @date 20 February 2011
 *
 * Copyright (C)2011 Etenil
 * 
 * All rights reserved.
 */

use Monolog\Logger;
use Monolog\Handler\SyslogHandler;
use Monolog\Handler\StreamHandler;

/**
 * Return the list of gender
 */
function getGender() {
    return array('N' => __('gender.none'),
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
            'none'          => __('marital.none'),
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
        'frustated'     => __('mood.frustrated'), // Suffering from frustration; dissatisfied, agitated, or discontented because one is unable to perform an action or fulfill a desire.
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

/**
 * @desc Return a small help to recognize flag color
 * */
function getFlagTitle($color){
    $title = '';
    switch($color){
        case 'white':
            $title = __('flag.white');
        break;
        
        case 'green':
            $title = __('flag.green');
        break;

        case 'orange':
            $title = __('flag.orange');
        break;

        case 'red':
            $title = __('flag.red');
        break;

        case 'black':
            $title = __('flag.black');
        break;

        default:
        break;
    }
    return $title;
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
 * @desc Request a simple url
 */
function requestURL($url, $timeout = 10, $post = false) {
    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_ENCODING, 'gzip,deflate');
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);

    if(is_array($post)) {
        $params = '';
        
        foreach($post as $key => $value) {
            $params .= $key . '=' . $value .'&';
        }
        curl_setopt ($ch, CURLOPT_POST, 1);
        curl_setopt ($ch, CURLOPT_POSTFIELDS, $params);
    }

    $rs = array();

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
?>
