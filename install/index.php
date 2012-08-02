<?php
require_once('../system/Lang/i18n.php');
require_once('../system/Lang/languages.php');

error_reporting(E_ALL);
ini_set('display_errors', '1');

$tmpfile = "../config/conf.xml.temp";
$conffile = "../config/conf.xml";

function get_mysql_port() {
    $port = ini_get('mysql.default_port');
    if($port == "")
        $port = ini_get('mysqli.default_port');
    if($port == "")
        $port = 3306;
    return $port;
}

$err = array();
function set_error($error_name, $error_message)
{
	global $err;
	$err[$error_name] = $error_message;
}

function err($error_name)
{
	global $err;
	if(isset($err[$error_name])) {
		return $err[$error_name];
	} else {
		return false;
	}
}

function has_errors()
{
	global $err;
	return count($err);
}



function list_themes()
{
  $dir = opendir('../themes');
  $themes = array();

  while($theme = readdir($dir)) {
    if(preg_match('/^\.+$/', $theme)
       || !is_dir('../themes/'.$theme)) {
      continue;
    }

    $themes[$theme] = $theme;
  }

  return $themes;
}

function list_lang()
{
  $dir = opendir('../i18n');
  $langs = array('en' => 'English');
  $languages = get_lang_list();

  while($lang = readdir($dir)) {
    if(!preg_match('/\.po$/', $lang)) {
      continue;
    }

    $lang = substr($lang, 0, strlen($lang) - 3);
    $langs[$lang] = $languages[$lang];
  }

  return $langs;
}

function get_checkbox($name, $if = 'true', $else = 'false')
{
  return (isset($_POST[$name])? $if : $else);
}

function test_bosh($boshhost, $port, $suffix, $host)
{
    $url = (get_checkbox('boshCookieHTTPS') == "true")? 'https://' : 'http://';

    $url .= $boshhost.":".$port.'/'.$suffix;

    $headers = array('Accept-Encoding: gzip, deflate', 'Content-Type: text/xml; charset=utf-8');
    $data = "
        <body content='text/xml; charset=utf-8'
              hold='1'
              rid='1573741820'
              to='".$host."'
              secure='true'
              wait='60'
              xml:lang='en'
              xmpp:version='1.0'
              xmlns='http://jabber.org/protocol/httpbind'
              xmlns:xmpp='urn:xmpp:xbosh'/>";

    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_ENCODING, 'gzip,deflate');
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_VERBOSE, false);

    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

    $rs = array();
    $rs['content'] = curl_exec($ch);
    $rs['errno'] = curl_errno($ch);
    $rs['errmsg'] = curl_error($ch);
    $rs['header'] = curl_getinfo($ch);

	if($rs['errno']) {
		set_error('bosh', t("Bosh connection failed with error '%s'", $rs['errmsg']));
		return false;
	}

    curl_close($ch);
    $arr = simplexml_load_string($rs["content"]);
    if(is_object($arr)) {
      $att = $arr->attributes();
      if($att['type'] == 'terminate') {
        set_error('bosh', t("XMPP connection through Bosh failed with error '%s'", $att['condition']));
      }
      else {
        $sid_set = isset($att['sid']);
        if(!$sid_set)
          set_error('bosh', "XMPP connection through Bosh returned invalid XML data");
        return ($sid_set);
      }
    }
    else {
      set_error('bosh', "XMPP connection through Bosh failed, check server parameters");
      return false;
    }
}

function test_dir($dir){
	return (file_exists($dir) && is_dir($dir) && is_writable($dir));
}
/*
 * Create the dirs 
 */

function create_dirs(){

  if(!test_dir('../cache') && !@mkdir('../cache')) {
    echo t("Couldn't create directory '%s'.", 'cache');
    return false;
  }
  if(!test_dir('../log') && !@mkdir('../log')) {
    echo t("Couldn't create directory '%s'.", 'log');
    return false;
  }
  if(!test_dir('../config') && !@mkdir('../config')) {
    echo t("Couldn't create directory '%s'.", 'config');
    return false;
  }
}

//Returns the content of the post, of the xml, or a placeholder string
function get_entry($post){
	global $xml;
	if(isset($_POST[$post])){
		return $_POST[$post];
	}elseif(isset($xml->$post)){
		return $xml->$post;
	}else{
		return "n/a";
	}
}

//Checks if movim already knows the user choice, or it returns a preset for the given form data
function get_preset_value($post, $preset){
	if(get_entry($post) == "n/a" || get_entry($post) == ""){
		return $preset;
	}else{
		return get_entry($post);
	}
}

function make_config(){
	global $xml;
	$conf = array(
	'config' => array(
	  'theme'              => get_entry('theme'),
	  'defLang'            => get_entry('defLang'),
//	  'boshCookieTTL'      => $_POST['boshCookieTTL'],
//	  'boshCookiePath'     => $_POST['boshCookiePath'],
//	  'boshCookieDomain'   => get_checkbox('boshCookieDomain'),
//	  'boshCookieHTTPS'    => get_checkbox('boshCookieHTTPS'),
//	  'boshCookieHTTPOnly' => get_checkbox('boshCookieHTTPOnly'),
	  'logLevel'           => get_entry('logLevel'),
	  //you should be able to do something with new pods, so:
	  'accountCreation'    => True,
//	  'host'               => $_POST['host'],
//	  'domain'             => $_POST['domain'],
//	  'defBoshHost'        => $_POST['defBoshHost'],
//	  'defBoshSuffix'      => $_POST['defBoshSuffix'],
//	  'defBoshPort'        => $_POST['defBoshPort'],
//	  'storageDriver'      => $_POST['datajar'],
//	  'storageConnection'  => $_POST['database'],
//	  'proxyEnabled'       => get_checkbox('proxyEnabled'),
//	  'proxyURL'           => $_POST['proxyURL'], 
//	  'proxyPort'          => $_POST['proxyPort'],
	  'maxUsers'           => get_entry('maxUsers'),
	  ),
	);
	if(!@file_put_contents('../config/conf.xml.temp', make_xml($conf))) {
	echo t("Couldn't create configuration file '%s'.", 'config/conf.xml.temp');
	return false;
	}
	
	return true;
}


function make_xml($stuff)
{
  static $level = 0;
  $buffer = "";

  // Putting the XML declaration
  if($level == 0) {
    $buffer = '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL;
  }

  // Indentation
  $indent = "";
  for($i = 0; $i < $level; $i++) {
    $indent.= "  ";
  }

  // Doing the job
  foreach($stuff as $tag => $value) {
    if(is_array($value)) {
      $buffer.= $indent.'<'.$tag.'>'.PHP_EOL;
      $level++;
      $buffer.= make_xml($value);
      $buffer.= $indent.'</'.$tag.'>'.PHP_EOL;
    } else {
      $buffer.= "$indent<$tag>$value</$tag>".PHP_EOL;
    }
  }

  $level--;
  return $buffer;
}



function generate_Tooltip($text, $background=True){
	$html = 'onmouseover=" elmnt = document.getElementById(\'leftside\'); elmnt.innerHTML=\''.$text.'\'; ';
	if($background){
		$html .= 'this.style.background = \'#F8F8F8\';" onmouseout="this.style.background=\'white\';"';
	}else{
		$html .= '"';
	}
	return $html;
}



$steps = array(
	t("Compatibility Check"),
	t("General Settings"),
	t("Database Settings"),
	t("Bosh Configuration"),
	t("XMPP Server"),
	t("Done")
	);



#################
#Handle the forms
###############
if(isset($_POST['back'])){
	$_POST['step'] -= 2;
}
//When the tests do not fail, we just create some directories and succeed to the next step
if(isset($_POST['step'])) {
	switch($_POST['step']){
		
		//The checks passed:
		case 0:{
			//We load the array.
			$xml = simplexml_load_file($tmpfile);
			create_dirs();
			$step = 1;
			break;
			
		//Store the basic settings
		}case 1:{
			//We load the array.
			$xml = simplexml_load_file($tmpfile);
			make_config();
			$step = 2;
			break;
			
		//Store the DB settings
		#TODO: Verify the SQL Settings
		}case 2: {
			//We load the array.
			$xml = simplexml_load_file($tmpfile);
			$step = 3;
			break;
			
		//The BOSH settings
		#TODO: Check if bosh settings are right and whether open Bosh (e.g. connect to random xmpp); when bosh closed warn the user
		}case 3: {
			$step = 4;
			break;
			
		#TODO: Display all Settings again
		}case 4: {
			break;
			
		#TOTO: Write Database; Rename conf.xml.part
		}case 5: {
			break;
		//If the user goes back to the checks:
		}case -1: {
			$step = 0;
			break;
			
		}default: die("Something went wrong");
	}
}else{
	$step = 0;
}


require('template.php');

?>
