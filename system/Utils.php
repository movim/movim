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

    $string = replaceAnchorsWithText($string);

    $string = preg_replace(
        array(
            '/(^|\s|>)(www.[^<> \n\r]+)/iex',
            '/(^|\s|>)([_A-Za-z0-9-]+(\\.[A-Za-z]{2,3})?\\.[A-Za-z]{2,4}\\/[^<> \n\r]+)/iex',
            '/(?(?=<a[^>]*>.+<\/a>)(?:<a[^>]*>.+<\/a>)|([^="\'])((?:https?):\/\/([^<> \n\r]+)))/iex'
        ),  
        array(
            "stripslashes((strlen('\\2')>0?'\\1<a href=\"http://\\2\" target=\"_blank\">\\2</a>&nbsp;\\2':'\\0'))",
            "stripslashes((strlen('\\2')>0?'\\1<a href=\"http://\\2\" target=\"_blank\">\\2</a>&nbsp;\\4':'\\0'))",
            "stripslashes((strlen('\\2')>0?'\\1<a href=\"\\2\" target=\"_blank\">\\2</a>&nbsp;':'\\0'))",
        ),  
        ' '.$string
    );
    
    $conf = new Conf();
    $theme = $conf->getServerConfElement('theme');
    
    $path = BASE_URI . 'themes/' . $theme . '/img/smileys/';

    foreach($smileys as $key => $value) {
        $replace = '<img class="smiley" src="'.$path.$value.'">';
        $string = str_replace($key, $replace, $string);
    }
    
    $string = preg_replace('#<script[^>]*>.*?</script>#is','',$string);
    

    return trim($string);
}

/**
 * Convert plaintext URI to HTML links.
 *
 * Converts URI, www and ftp, and email addresses. Finishes by fixing links
 * within links.
 *
 * @since 0.71
 *
 * @param string $text Content to convert URIs.
 * @return string Content with converted URIs.
 */
function make_clickable( $text ) {
	$r = '';
	$textarr = preg_split( '/(<[^<>]+>)/', $text, -1, PREG_SPLIT_DELIM_CAPTURE ); // split out HTML tags
	foreach ( $textarr as $piece ) {
		if ( empty( $piece ) || ( $piece[0] == '<' && ! preg_match('|^<\s*[\w]{1,20}+://|', $piece) ) ) {
			$r .= $piece;
			continue;
		}

		// Long strings might contain expensive edge cases ...
		if ( 10000 < strlen( $piece ) ) {
			// ... break it up
			foreach ( _split_str_by_whitespace( $piece, 2100 ) as $chunk ) { // 2100: Extra room for scheme and leading and trailing paretheses
				if ( 2101 < strlen( $chunk ) ) {
					$r .= $chunk; // Too big, no whitespace: bail.
				} else {
					$r .= make_clickable( $chunk );
				}
			}
		} else {
			$ret = " $piece "; // Pad with whitespace to simplify the regexes

			$url_clickable = '~
				([\\s(<.,;:!?])                                        # 1: Leading whitespace, or punctuation
				(                                                      # 2: URL
					[\\w]{1,20}+://                                # Scheme and hier-part prefix
					(?=\S{1,2000}\s)                               # Limit to URLs less than about 2000 characters long
					[\\w\\x80-\\xff#%\\~/@\\[\\]*(+=&$-]*+         # Non-punctuation URL character
					(?:                                            # Unroll the Loop: Only allow puctuation URL character if followed by a non-punctuation URL character
						[\'.,;:!?)]                            # Punctuation URL character
						[\\w\\x80-\\xff#%\\~/@\\[\\]*(+=&$-]++ # Non-punctuation URL character
					)*
				)
				(\)?)                                                  # 3: Trailing closing parenthesis (for parethesis balancing post processing)
			~xS'; // The regex is a non-anchored pattern and does not have a single fixed starting character.
			      // Tell PCRE to spend more time optimizing since, when used on a page load, it will probably be used several times.

			$ret = preg_replace_callback( $url_clickable, '_make_url_clickable_cb', $ret );

			$ret = preg_replace_callback( '#([\s>])((www|ftp)\.[\w\\x80-\\xff\#$%&~/.\-;:=,?@\[\]+]+)#is', '_make_web_ftp_clickable_cb', $ret );
			$ret = preg_replace_callback( '#([\s>])([.0-9a-z_+-]+)@(([0-9a-z-]+\.)+[0-9a-z]{2,})#i', '_make_email_clickable_cb', $ret );

			$ret = substr( $ret, 1, -1 ); // Remove our whitespace padding.
			$r .= $ret;
		}
	}

	// Cleanup of accidental links within links
	$r = preg_replace( '#(<a( [^>]+?>|>))<a [^>]+?>([^>]+?)</a></a>#i', "$1$3</a>", $r );
	return $r;
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
