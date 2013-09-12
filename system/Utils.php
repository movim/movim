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

// Handy.
function println($string)
{
    $args = func_get_args();
    echo call_user_func_array('sprintf', $args) . PHP_EOL;
}

function sprintln($string)
{
    $args = func_get_args();
    return call_user_func_array('sprintf', $args) . PHP_EOL;
}

/**
 * Prepare the string (add the a to the links and show the smileys)
 *
 * @param string $string
 * @return string
 */
function prepareString($string) {
    $smileys = 
        array(
            // HFR icons
            ":'\("  => 'cry.gif',
            ':love:'=> 'love.gif',
            'O:\)'  => 'ange.gif',
            'O:-\)' => 'ange.gif',
            ':redface:' => 'redface.gif',
            ':petrus:'  => 'petrus75.gif',
            
            // famfamfam icons
            ':\)\)' => 'grin.png',
            ':\)'   => 'smiley.png',
            ':-\)'  => 'smiley.png',
            ':\('   => 'sad.png',
            ':o'    => 'shocked.png',
            ':O'    => 'shocked.png',
            ':D'    => 'grin.png',
            ':d'    => 'grin.png',
            ':p'    => 'tongue.png',
            ':P'    => 'tongue.png',
            ':-P'   => 'tongue.png',
            ';D'    => 'wink.png',
            ';d'    => 'wink.png',
            ';\)'   => 'wink.png',
            '\^\^'  => 'happy.png',
            '\(k\)' => 'heart.png',
            'B\)'   => 'cool.png',
            ':s'    => 'confused.png',
            ':S'    => 'confused.png',
            ':\/'   => 'wondering.png',
            ':evil:'=> 'evil.png',
            ":\|"   => 'neutral.png',
            
            // Meme icons
            ':okay:'        => 'okay.gif',
            ':trolldad:'    => 'trolldad.png',
            ':epic:'        => 'epic.png',
            ':aloneyeah:'   => 'aloneyeah.png',
            ':fapfap:'      => 'fapfap.png',
            ':megusta:'     => 'gusta.png',
            ':trollface:'   => 'trollface.png',
            ':troll:'       => 'trollface.png',
            ':lol:'         => 'trollol.png',
            ':genius:'      => 'genius.png',
        );
    
    //$string = str_replace('<a ', '<a target="_blank" ', $string);
    
    //replace begin by www
    $string = preg_replace_callback(
            '/(^|\s|>)(www.[^<> \n\r]+)/ix', function ($match) {
                //print '<br />preg[1]';\system\Debug::dump($match);
                if (strlen($match[2])>0) {
                    return stripslashes($match[1].'<a href=\"http://'.$match[2].'\" target=\"_blank\">'.$match[2].'</a>');
                } else {
                    return $match[2];
                }
            }, ' ' . $string
    );

    //replace  begin by http - https (before www)
    $string = preg_replace_callback(
            '/(?(?=<a[^>]*>.+<\/a>)(?:<a[^>]*>.+<\/a>)|([^="\'])((?:https?):\/\/([^<> \n\r]+)))/ix', function ($match) {
                //print '<br />preg[2]';\system\Debug::dump($match);
                if (isset($match[2]) && strlen($match[2])>0) {
                    return stripslashes($match[1].'<a href=\"'.$match[2].'\" target=\"_blank\">'.$match[3].'</a>');
                } else {
                    return $match[0];
                }
            }, ' ' . $string
    );

    //remove all scripts
    $string = preg_replace_callback(
            '#<[/]?script[^>]*>#is', function ($match) {
                return '';
            }, ' ' . $string
    );
    //remove all iframe
    $string = preg_replace_callback(
            '#<[/]?iframe[^>]*>#is', function ($match) {
                return '';
            }, ' ' . $string
    );
   
    // We add some smileys...
    $conf = new \system\Conf();
    $theme = $conf->getServerConfElement('theme');
    
    $path = BASE_URI . 'themes/' . $theme . '/img/smileys/';

    foreach($smileys as $key => $value) {
        $replace = ' <img class="smiley" src="'.$path.$value.'">';
        $string = preg_replace('/(^|[ ])('.$key.')/',  $replace, $string);
    }
    
    return trim($string);
}

function cleanHTMLTags($string) {
    return str_replace(
        array(
            '<content type="html">',
            '<html xmlns="http://jabber.org/protocol/xhtml-im">',
            '<body xmlns="http://www.w3.org/1999/xhtml">',
            '</body>',
            '</html>', 
            '</content>'),
        '',
        $string);
}

/**
 * Return a human-readable date 
 *
 * @param timestamp $string
 * @return string
 */
function prepareDate($time, $hours = true) {

    $dotw = array(
        1 => t('Monday'),
        2 => t('Tuesday'),
        3 => t('Wednesday'),
        4 => t('Thursday'),
        5 => t('Friday'),
        6 => t('Saturday'),
        7 => t('Friday'));
        
    $moty = array(
        1 => t('January'),
        2 => t('February'),
        3 => t('March'),
        4 => t('April'),
        5 => t('May'),
        6 => t('June'),
        7 => t('July'),
        8 => t('August'),
        9 => t('September'),
        10 => t('October'),
        11 => t('November'),
        12 => t('December'));

    $today = strtotime(date('M j, Y'));
    $reldays = ($time - $today)/86400;

    if ($reldays >= 0 && $reldays < 1) {
        $date = t('Today');
    } else if ($reldays >= 1 && $reldays < 2) {
        $date = t('Tomorrow');
    } else if ($reldays >= -1 && $reldays < 0) {
        $date = t('Yesterday');
    } else {

        if (abs($reldays) < 7) {
            if ($reldays > 0) {
                $reldays = floor($reldays);
                $date = 'In ' . $reldays . ' '.t('day') . ($reldays != 1 ? 's' : '');
            } else {
                $reldays = abs(floor($reldays));
                $date = t(' %d days ago', $reldays);
            }
        } else {
            $date = $dotw[date('N',$time ? $time : time())] .', '.date('j',$time ? $time : time()).' '.$moty[date('n',$time ? $time : time())] ;
            if (abs($reldays) > 182)
                $date .= date(', Y',$time ? $time : time());
        }
    }
    if($hours)
        $date .= ' - '. date('H:i', $time);
    
    if($time)
        return $date;
}

/**
 * Return the list of gender
 */
function getGender() {
    return array('N' => t('None'),
                    'M' => t('Male'),
                    'F' => t('Female'),
                    'O' => t('Other')
                    );
}

/**
 * Return an array of informations from a XMPP uri
 */
function explodeURI($uri) {
    $arr = parse_url(urldecode($uri));
    $result = array();
    
    if(isset($arr['query'])) {
        $query = explode(';', $arr['query']);


        foreach($query as $elt) {
            if($elt != '') {
                list($key, $val) = explode('=', $elt);
                $result[$key] = $val;
            }
        }

        $arr = array_merge($arr, $result);
    }
    
    return $arr;

}

/**
 * Return a list of all the country
 */
function getCountries() {
    return array(
        "Afghanistan",
        "Albania",
        "Algeria",
        "Andorra",
        "Angola",
        "Antigua and Barbuda",
        "Argentina",
        "Armenia",
        "Australia",
        "Austria",
        "Azerbaijan",
        "Bahamas",
        "Bahrain",
        "Bangladesh",
        "Barbados",
        "Belarus",
        "Belgium",
        "Belize",
        "Benin",
        "Bhutan",
        "Bolivia",
        "Bosnia and Herzegovina",
        "Botswana",
        "Brazil",
        "Brunei",
        "Bulgaria",
        "Burkina Faso",
        "Burundi",
        "Cambodia",
        "Cameroon",
        "Canada",
        "Cape Verde",
        "Central African Republic",
        "Chad",
        "Chile",
        "China",
        "Colombi",
        "Comoros",
        "Congo (Brazzaville)",
        "Congo",
        "Costa Rica",
        "Cote d'Ivoire",
        "Croatia",
        "Cuba",
        "Cyprus",
        "Czech Republic",
        "Denmark",
        "Djibouti",
        "Dominica",
        "Dominican Republic",
        "East Timor (Timor Timur)",
        "Ecuador",
        "Egypt",
        "El Salvador",
        "Equatorial Guinea",
        "Eritrea",
        "Estonia",
        "Ethiopia",
        "Fiji",
        "Finland",
        "France",
        "Gabon",
        "Gambia, The",
        "Georgia",
        "Germany",
        "Ghana",
        "Greece",
        "Grenada",
        "Guatemala",
        "Guinea",
        "Guinea-Bissau",
        "Guyana",
        "Haiti",
        "Honduras",
        "Hungary",
        "Iceland",
        "India",
        "Indonesia",
        "Iran",
        "Iraq",
        "Ireland",
        "Israel",
        "Italy",
        "Jamaica",
        "Japan",
        "Jordan",
        "Kazakhstan",
        "Kenya",
        "Kiribati",
        "Korea, North",
        "Korea, South",
        "Kuwait",
        "Kyrgyzstan",
        "Laos",
        "Latvia",
        "Lebanon",
        "Lesotho",
        "Liberia",
        "Libya",
        "Liechtenstein",
        "Lithuania",
        "Luxembourg",
        "Macedonia",
        "Madagascar",
        "Malawi",
        "Malaysia",
        "Maldives",
        "Mali",
        "Malta",
        "Marshall Islands",
        "Mauritania",
        "Mauritius",
        "Mexico",
        "Micronesia",
        "Moldova",
        "Monaco",
        "Mongolia",
        "Morocco",
        "Mozambique",
        "Myanmar",
        "Namibia",
        "Nauru",
        "Nepa",
        "Netherlands",
        "New Zealand",
        "Nicaragua",
        "Niger",
        "Nigeria",
        "Norway",
        "Oman",
        "Pakistan",
        "Palau",
        "Panama",
        "Papua New Guinea",
        "Paraguay",
        "Peru",
        "Philippines",
        "Poland",
        "Portugal",
        "Qatar",
        "Romania",
        "Russia",
        "Rwanda",
        "Saint Kitts and Nevis",
        "Saint Lucia",
        "Saint Vincent",
        "Samoa",
        "San Marino",
        "Sao Tome and Principe",
        "Saudi Arabia",
        "Senegal",
        "Serbia and Montenegro",
        "Seychelles",
        "Sierra Leone",
        "Singapore",
        "Slovakia",
        "Slovenia",
        "Solomon Islands",
        "Somalia",
        "South Africa",
        "Spain",
        "Sri Lanka",
        "Sudan",
        "Suriname",
        "Swaziland",
        "Sweden",
        "Switzerland",
        "Syria",
        "Taiwan",
        "Tajikistan",
        "Tanzania",
        "Thailand",
        "Togo",
        "Tonga",
        "Trinidad and Tobago",
        "Tunisia",
        "Turkey",
        "Turkmenistan",
        "Tuvalu",
        "Uganda",
        "Ukraine",
        "United Arab Emirates",
        "United Kingdom",
        "United States",
        "Uruguay",
        "Uzbekistan",
        "Vanuatu",
        "Vatican City",
        "Venezuela",
        "Vietnam",
        "Yemen",
        "Zambia",
        "Zimbabwe"
    );
}

/**
 * Return the list of marital status
 */
function getMarital() {
    return array('none' => t('None'),
                    'single' => t('Single'),
                    'relationship' => t('In a relationship'),
                    'married' => t('Married'),
                    'divorced' => t('Divorced'),
                    'widowed' => t('Widowed'),
                    'cohabiting' => t('Cohabiting'),
                    'union' => t('Civil Union')
                    );                      
}

function getPresences() {
    return array(
                1 => t('Online'),
                2 => t('Away'),
                3 => t('Do Not Disturb'),
                4 => t('Extended Away'),
                5 => t('Logout')
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
        'afraid' => t('afraid'), // Impressed with fear or apprehension; in fear; apprehensive.
        'amazed' => t('amazed'), // Astonished; confounded with fear, surprise or wonder.
        'amorous' => t('amorous'), // Inclined to love; having a propensity to love, or to sexual enjoyment; loving, fond, affectionate, passionate, lustful, sexual, etc.
        'angry' => t('angry'), // Displaying or feeling anger, i.e., a strong feeling of displeasure, hostility or antagonism towards someone or something, usually combined with an urge to harm.
        'annoyed' => t('annoyed'), // To be disturbed or irritated, especially by continued or repeated acts.
        'anxious' => t('anxious'), // Full of anxiety or disquietude; greatly concerned or solicitous, esp. respecting something future or unknown; being in painful suspense.
        'aroused' => t('aroused'), // To be stimulated in one's feelings, especially to be sexually stimulated.
        'ashamed' => t('ashamed'), // Feeling shame or guilt.
        'bored' => t('bored'), // Suffering from boredom; uninterested, without attention.
        'brave' => t('brave'), // Strong in the face of fear; courageous.
        'calm' => t('calm'), // Peaceful, quiet.
        'cautious' => t('cautious'), // Taking care or caution; tentative.
        'cold' => t('cold'), // Feeling the sensation of coldness, especially to the point of discomfort.
        'confident' => t('confident'), // Feeling very sure of or positive about something, especially about one's own capabilities.
        'condused' => t('confused'), // Chaotic, jumbled or muddled.
        'contemplative' => t('contemplative'), // Feeling introspective or thoughtful.
        'contented' => t('contented'), // Pleased at the satisfaction of a want or desire; satisfied.
        'cranzy' => t('cranky'), // Grouchy, irritable; easily upset.
        'crazy' => t('crazy'), // Feeling out of control; feeling overly excited or enthusiastic.
        'creative' => t('creative'), // Feeling original, expressive, or imaginative.
        'curious' => t('curious'), // Inquisitive; tending to ask questions, investigate, or explore.
        'dejected' => t('dejected'), // Feeling sad and dispirited.
        'depressed' => t('depressed'), // Severely despondent and unhappy.
        'disappointed' => t('disappointed'), // Defeated of expectation or hope; let down.
        'disgusted' => t('disgusted'), // Filled with disgust; irritated and out of patience.
        'dismayed' => t('dismayed'), // Feeling a sudden or complete loss of courage in the face of trouble or danger.
        'distracted' => t('distracted'), // Having one's attention diverted; preoccupied.
        'embarrassed' => t('embarrassed'), // Having a feeling of shameful discomfort.
        'envious' => t('envious'), // Feeling pain by the excellence or good fortune of another.
        'excited' => t('excited'), // Having great enthusiasm.
        'flirtatious' => t('flirtatious'), // In the mood for flirting.
        'frustated' => t('frustrated'), // Suffering from frustration; dissatisfied, agitated, or discontented because one is unable to perform an action or fulfill a desire.
        'grateful' => t('grateful'), // Feeling appreciation or thanks.
        'grieving' => t('grieving'), // Feeling very sad about something, especially something lost; mournful; sorrowful.
        'grumpy' => t('grumpy'), // Unhappy and irritable.
        'guilty' => t('guilty'), // Feeling responsible for wrongdoing; feeling blameworthy.
        'happy' => t('happy'), // Experiencing the effect of favourable fortune; having the feeling arising from the consciousness of well-being or of enjoyment; enjoying good of any kind, as peace, tranquillity, comfort; contented; joyous.
        'hopeful' => t('hopeful'), // Having a positive feeling, belief, or expectation that something wished for can or will happen.
        'hot' => t('hot'), // Feeling the sensation of heat, especially to the point of discomfort.
        'humbled' => t('humbled'), // Having or showing a modest or low estimate of one's own importance; feeling lowered in dignity or importance.
        'humiliated' => t('humiliated'), // Feeling deprived of dignity or self-respect.
        'hungry' => t('hungry'), // Having a physical need for food.
        'hurt' => t('hurt'), // Wounded, injured, or pained, whether physically or emotionally.
        'impressed' => t('impressed'), // Favourably affected by something or someone.
        'in_awe' => t('in awe'), // Feeling amazement at something or someone; or feeling a combination of fear and reverence.
        'in_love' => t('in love'), // Feeling strong affection, care, liking, or attraction..
        'indignant' => ('indignant'), // Showing anger or indignation, especially at something unjust or wrong.
        'interested' => t('interested'), // Showing great attention to something or someone; having or showing interest.
        'intoxicated' => t('intoxicated'), // Under the influence of alcohol; drunk.
        'invincible' => t('invincible'), // Feeling as if one cannot be defeated, overcome or denied.
        'jealous' => t('jealous'), // Fearful of being replaced in position or affection.
        'lonely' => t('lonely'), // Feeling isolated, empty, or abandoned.
        'lost' => t('lost'), // Unable to find one's way, either physically or emotionally.
        'lucky' => t('lucky'), // Feeling as if one will be favored by luck.
        'mean' => t('mean'), // Causing or intending to cause intentional harm; bearing ill will towards another; cruel; malicious.
        'moody' => t('moody'), // Given to sudden or frequent changes of mind or feeling; temperamental.
        'nervous' => t('nervous'), // Easily agitated or alarmed; apprehensive or anxious.
        'neutral' => t('neutral'), // Not having a strong mood or emotional state.
        'offended' => t('offended'), // Feeling emotionally hurt, displeased, or insulted.
        'outraged' => t('outraged'), // Feeling resentful anger caused by an extremely violent or vicious attack, or by an offensive, immoral, or indecent act.
        'playful' => t('playful'), // Interested in play; fun, recreational, unserious, lighthearted; joking, silly.
        'proud' => t('proud'), // Feeling a sense of one's own worth or accomplishment.
        'relaxed' => t('relaxed'), // Having an easy-going mood; not stressed; calm.
        'relieved' => t('relieved'), // Feeling uplifted because of the removal of stress or discomfort.
        'remorseful' => t('remorseful'), // Feeling regret or sadness for doing something wrong.
        'restless' => t('restless'), // Without rest; unable to be still or quiet; uneasy; continually moving.
        'sad' => t('sad'), // Feeling sorrow; sorrowful, mournful.
        'sarcastic' => t('sarcastic'), // Mocking and ironical.
        'satisfied' => t('satisfied'), // Pleased at the fulfillment of a need or desire.
        'serious' => t('serious'), // Without humor or expression of happiness; grave in manner or disposition; earnest; thoughtful; solemn.
        'shocked' => t('shocked'), // Surprised, startled, confused, or taken aback.
        'shy' => t('shy'), // Feeling easily frightened or scared; timid; reserved or coy.
        'sick' => t('sick'), // Feeling in poor health; ill.
        'sleepy' => t('sleepy'), // Feeling the need for sleep.
        'spontaneous' => t('spontaneous'), // Acting without planning; natural; impulsive.
        'stressed' => t('stressed'), // Suffering emotional pressure.
        'strong' => t('strong'), // Capable of producing great physical force; or, emotionally forceful, able, determined, unyielding.
        'surprised' => t('surprised'), // Experiencing a feeling caused by something unexpected.
        'thankful' => t('thankful'), // Showing appreciation or gratitude.
        'thirsty' => t('thirsty'), // Feeling the need to drink.
        'tired' => t('tired'), // In need of rest or sleep.
        'undefined' => t('undefined'), // [Feeling any emotion not defined here.]
        'weak' => t('weak'), // Lacking in force or ability, either physical or emotional.
        'worried' => t('worried') // Thinking about unpleasant things that have happened or that might happen; feeling afraid and unhappy.
    );
}

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

/*
 * Echap the JID 
 */
function echapJid($jid)
{
    return str_replace(' ', '\40', $jid);
}

/**
 * Return a URIfied string
 * @param string
 * @return string
 */
function stringToUri($url) {
    $url = utf8_decode($url);
    $url = strtolower(strtr($url, utf8_decode('ÀÁÂÃÄÅàáâãäåÒÓÔÕÖØòóôõöøÈÉÊËèéêëÇçÌÍÎÏìíîïÙÚÛÜùúûüÿÑñ()[]\'"~$&%*@ç!?;,:/\^¨€{}<>|+.- '),  'aaaaaaaaaaaaooooooooooooeeeeeeeecciiiiiiiiuuuuuuuuynn    --      c  ---    e       --'));
    $url = str_replace(' ', '', $url);
    $url = str_replace('---', '-', $url);
    $url = str_replace('--', '-', $url);
    $url = trim($url,'-');
    return $url;
}

/**
 * Return a human readable filesize
 * @param string size in bytes
 * @return string
 */
function sizeToCleanSize($size)
{
    $units = array( 'B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
    $power = $size > 0 ? floor(log($size, 1024)) : 0;
    return number_format($size / pow(1024, $power), 2, '.', ',') . ' ' . $units[$power];
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


function movim_log($log) {
    \system\Logs\Logger::log($log);
}

/**
 * Return a small help to recognize flag color
 * */
function getFlagTitle($color){
    $title="";
    switch($color){
        case 'white':
        $title=t('Not shared');
    break;
        
        case 'green':
            $title=t('Shared with one contact');
        break;

        case 'orange':
            $title=t('Shared with all contacts');
        break;

        case 'red':
            $title=t('Shared with the XMPP network');
        break;

        case 'black':
            $title=t('Shared with the whole Internet');
        break;

        default:
        break;
    }
    return $title;
}
?>
