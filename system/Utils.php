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

/*
 * Return the current microtime
 */
function getTime()
{
    $a = explode (' ',microtime());
    return(double) $a[0] + $a[1];
}

/**
 * Replaces anchor tags with text
 * - Will search string and replace all anchor tags with text (case insensitive)
 *
 * How it works:
 * - Searches string for an anchor tag, checks to make sure it matches the criteria
 *         Anchor search criteria:
 *             - 1 - <a (must have the start of the anchor tag )
 *             - 2 - Can have any number of spaces or other attributes before and after the href attribute
 *             - 3 - Must close the anchor tag
 *
 * - Once the check has passed it will then replace the anchor tag with the string replacement
 * - The string replacement can be customized
 *
 * Know issue:
 * - This will not work for anchors that do not use a ' or " to contain the attributes.
 *         (i.e.- <a href=http: //php.net>PHP.net</a> will not be replaced)
 */
function replaceAnchorsWithText($data) {
    /**
     * Had to modify $regex so it could post to the site... so I broke it into 6 parts.
     */
    $regex  = '/(<a\s*'; // Start of anchor tag
    $regex .= '(.*?)\s*'; // Any attributes or spaces that may or may not exist
    $regex .= 'href=[\'"]+?\s*(?P<link>\S+)\s*[\'"]+?'; // Grab the link
    $regex .= '\s*(.*?)\s*>\s*'; // Any attributes or spaces that may or may not exist before closing tag
    $regex .= '(?P<name>\S+)'; // Grab the name
    $regex .= '\s*<\/a>)/i'; // Any number of spaces between the closing anchor tag (case insensitive)
   
    if (is_array($data)) {
        // This is what will replace the link (modify to you liking)
        $data = "{$data['name']} {$data['link']} ";
    }
    return preg_replace_callback($regex, 'replaceAnchorsWithText', $data);
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
            ':okay:' => 'okay.gif',
            'O:)' => 'ange.gif',
            'O:-)' => 'ange.gif',
            ':)' => 'smile.gif',
            ':-)' => 'smile.gif',
            ':(' => 'frown.gif',
            ':o' => 'redface.gif',
            ':love:' => 'love.gif',
            '<3' => 'love.gif',
            ':D' => 'biggrin.gif',
            ':d' => 'biggrin.gif',
            ':p' => 'tongue.gif',
            ':P' => 'tongue.gif',
            ':-P' => 'tongue.gif',
            ' :/' => 'bof.gif', // Here we add a space to prevent URL parse error in the second part of the function
            ';)' => 'wink.gif',
            'B)' => 'sol.gif',
            ":'(" => 'cry.gif',
            ':trolldad:' => 'trolldad.png',
            ':epic:' => 'epic.png',
            ':aloneyeah:' => 'aloneyeah.png',
            ':fapfap:' => 'fapfap.png',
            ':megusta:' => 'gusta.png',
            ':trollface:' => 'trollface.png',
            ':troll:' => 'trollface.png',
            ':lol:' => 'trollol.png',
        );
    
    $fixer = new HtmlFixer();
    $string = $fixer->getFixedHtml($string);
    
    $string = str_replace('<a ', '<a target="_blank" ', $string);

    
    $string = preg_replace(
        array(
            '/(^|\s|>)(www.[^<> \n\r]+)/iex',
            '/(^|\s|>)([_A-Za-z0-9-]+(\\.[A-Za-z]{2,3})?\\.[A-Za-z]{2,4}\\/[^<> \n\r]+)/iex',
            '/(?(?=<a[^>]*>.+<\/a>)(?:<a[^>]*>.+<\/a>)|([^="\'])((?:https?):\/\/([^<> \n\r]+)))/iex',
            '#<script[^>]*>.*?</script>#is'
        ),  
        array(
            "stripslashes((strlen('\\2')>0?'\\1<a href=\"http://\\2\" target=\"_blank\">\\2</a>&nbsp;\\2':'\\0'))",
            "stripslashes((strlen('\\2')>0?'\\1<a href=\"http://\\2\" target=\"_blank\">\\2</a>&nbsp;\\4':'\\0'))",
            "stripslashes((strlen('\\2')>0?'\\1<a href=\"\\2\" target=\"_blank\">\\2</a>&nbsp;':'\\0'))",
            ''
        ),  
        ' '.$string
    );
    
    // We add some smileys...    
    $conf = new Conf();
    $theme = $conf->getServerConfElement('theme');
    
    $path = BASE_URI . 'themes/' . $theme . '/img/smileys/';

    foreach($smileys as $key => $value) {
        $replace = '<img class="smiley" src="'.$path.$value.'">';
        $string = str_replace($key, $replace, $string);
    }

    return trim($string);
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
    
    return $date;
}

/**
 * Generate a ramdom hash
 *
 * @return string
 */
function generateHash(){
    $result = "";
    $charPool = '0123456789abcdefghijklmnopqrstuvwxyz';

    for($p = 0; $p<15; $p++)
        $result .= $charPool[mt_rand(0,strlen($charPool)-1)];

    return sha1($result);
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

/*
 * Get the user local timezone
 */
function getLocalTimezone()
{
    $iTime = time();
    $arr = localtime($iTime);
    $arr[5] += 1900;
    $arr[4]++;
    $iTztime = gmmktime($arr[2], $arr[1], $arr[0], $arr[4], $arr[3], $arr[5]);
    $offset = doubleval(($iTztime-$iTime)/(60*60));
    $zonelist =
    array
    (
        'Kwajalein' => -12.00,
        'Pacific/Midway' => -11.00,
        'Pacific/Honolulu' => -10.00,
        'America/Anchorage' => -9.00,
        'America/Los_Angeles' => -8.00,
        'America/Denver' => -7.00,
        'America/Tegucigalpa' => -6.00,
        'America/New_York' => -5.00,
        'America/Caracas' => -4.30,
        'America/Halifax' => -4.00,
        'America/St_Johns' => -3.30,
        'America/Argentina/Buenos_Aires' => -3.00,
        'America/Sao_Paulo' => -3.00,
        'Atlantic/South_Georgia' => -2.00,
        'Atlantic/Azores' => -1.00,
        'Europe/Dublin' => 0,
        'Europe/Belgrade' => 1.00,
        'Europe/Minsk' => 2.00,
        'Asia/Kuwait' => 3.00,
        'Asia/Tehran' => 3.30,
        'Asia/Muscat' => 4.00,
        'Asia/Yekaterinburg' => 5.00,
        'Asia/Kolkata' => 5.30,
        'Asia/Katmandu' => 5.45,
        'Asia/Dhaka' => 6.00,
        'Asia/Rangoon' => 6.30,
        'Asia/Krasnoyarsk' => 7.00,
        'Asia/Brunei' => 8.00,
        'Asia/Seoul' => 9.00,
        'Australia/Darwin' => 9.30,
        'Australia/Canberra' => 10.00,
        'Asia/Magadan' => 11.00,
        'Pacific/Fiji' => 12.00,
        'Pacific/Tongatapu' => 13.00
    );
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
 * Check the current Jid
 *
 * @param string $jid
 * @return bool
 */
function checkJid($jid)
{
    return filter_var($jid, FILTER_VALIDATE_EMAIL);
}

function movim_log($log) {
	ob_start();
//    var_dump($log);
	print_r($log);
	$dump = ob_get_clean();
	$fh = fopen(BASE_PATH . 'log/movim.log', 'w');
	fwrite($fh, $dump);
	fclose($fh);
}

?>
