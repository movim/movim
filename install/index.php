<?php
require_once('../system/Lang/i18n.php');
require_once('../system/Lang/languages.php');
include_once('../system/Datajar2/Datajar/loader.php');

error_reporting(E_ALL);
ini_set('display_errors', '1');

$tmpfile = "../config/conf.xml.temp";
$conffile = "../config/conf.xml";
$errors = array();
$title = t('Movim Installer and Configuration Manager');
$djloaded = False;


function get_mysql_port() {
    $port = ini_get('mysql.default_port');
    if($port == "")
        $port = ini_get('mysqli.default_port');
    if($port == "")
        $port = 3306;
    return $port;
}

function parse_db_string($string){
	$matches = array();
	if(preg_match('%^([^/]+?)://(?:([^/@]*?)(?::([^/@:]+?)@)?([^/@:]+?)(?::([^/@:]+?))?)?/(.+)$%', $string, $matches)){
		return array('type' 	=> $matches[1],
				 'username' => $matches[2],
				 'password' => $matches[3],
				 'host'     => $matches[4],
				 'port'     => $matches[5],
				 'database' => $matches[6]);
	}else{
		return False;
	}
}

function generate_db_string($arr){
	if($arr['type'] != 'sqlite'){
		return $arr['type'].'://'.$arr['username'].':'.$arr['password'].'@'.$arr['host'].':'.$arr['port'].'/'.$arr['database'];
    }else{
		return $arr['type'].'://sqlite:sqlite@sqlite/'.$arr['database'];
	}
}
$err = array();
function set_error($error_name, $error_message)
{
	global $err;
	$err[$error_name] = $error_message;
}

function is_valid($what){
	global $errors;
	if($what){
		echo "message success";
	}else{
		echo "message error";
		$errors[] = True;
	}
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

//For the db-form we need to use this:
function get_preset_value_db($what, $preset){
	$entry = get_entry('db');
	$arr = parse_db_string($entry);
	if($arr){
		return $arr[$what];
	}else{
		return $preset;
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
	global $file;

	$conf = array(
	'config' => array(
	  'theme'              => get_entry('theme'),
	  'defLang'            => get_entry('defLang'),
	  'maxUsers'           => get_entry('maxUsers'),
	  'logLevel'           => get_entry('logLevel'),
	  'db'				   => get_entry('db'),
	  'boshUrl' 		   => get_entry('boshUrl'),
	  'userDefinedBosh'    => get_entry('userDefinedBosh'),
	  'boshOpen'		   => get_entry('boshOpen'),
	  'boshProxy'		   => get_entry('boshProxy')
	  ),
	);
	if(!@file_put_contents($file, make_xml($conf))){
		die(t("Couldn't create configuration file '%s'.", $file));
	}
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
	$html = 'style="background: url(../themes/movim/img/icons/follow_icon.png) no-repeat right; padding-right: 20px;" onmouseover=" elmnt = document.getElementById(\'leftside\'); elmnt.innerHTML=\''.$text.'\'; ';
	if($background){
		$html .= 'this.style.background = \'#F8F8F8 url(../themes/movim/img/icons/follow_icon.png) no-repeat right\';" onmouseout="this.style.background = \'white url(../themes/movim/img/icons/follow_icon.png) no-repeat right\';"';
	}else{
		$html .= '"';
	}
	return $html;
}

function authenticate(){
	header('WWW-Authenticate: Basic realm="Enter admin username/password"');
	header('HTTP/1.0 401 Unauthorized');
	echo 'Why are you hitting cancel?';
	exit;
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

//We have already a running install
if(file_exists($conffile)){
	//!!!!!
	$xml = simplexml_load_file($conffile);
	if (!isset($_SERVER['PHP_AUTH_USER'])) {
		authenticate();
	}else{
		if($_SERVER['PHP_AUTH_USER'] = $xml->user && $_SERVER['PHP_AUTH_PW'] == $xml->pw){
			$file = $conffile;
		}else{
			authenticate();
		}
	}
}else{
	$file = $tmpfile;
}

//When the tests do not fail, we just create some directories and succeed to the next step
if(isset($_POST['step'])) {
	$handle = $_POST['step'];
	$display = $handle + 1;
	if(isset($_POST['back'])){
		$display -= 2;
	}
	switch($handle){

		//The checks passed:
		case 0:{
			//Create the dirs
			create_dirs();
			if (!file_exists($file)){
				if(!@file_put_contents($file, '<?xml version="1.0" encoding="UTF-8"?><config></config>')){
					die("Cannot write to the config file!!!!!");
				}
			}
			$xml = simplexml_load_file($file);
			break;

		//Store the basic settings
		}case 1:{
			//We load the array.
			$xml = simplexml_load_file($file);
			make_config();
			break;

		//Store the DB settings
		#TODO: Verify the SQL Settings
		}case 2: {
			//The @ for some undefined vars^^
			$dbarray = @array('type' => $_POST['dbtype'] , 'username'=> $_POST['dbusername'] , 'password' => $_POST['dbpassword'] , 'host' => $_POST['dbhost'], 'port' => $_POST['dbport'], 'database' => $_POST['dbdatabase'] );
			$_POST['db'] = generate_db_string($dbarray);
			#ToDo: Test Connection for Mongo
			if(!$djloaded){
				load_datajar();
				$djloaded = True;
			}
			//This should pass without errors, as we already tested the backend before
			datajar_load_driver($dbarray['type']);
			DatajarEngineWrapper::setdriver($dbarray['type']);
			//This can fail:
			try{
				$sdb = new DatajarEngineWrapper(generate_db_string($dbarray));
				DatajarBase::bind($sdb);
			}catch(DatajarException $e){
				//Append it to the error array
				$errors[] = $e->getMessage();
				//The page is displayed again, to correct the mistakes
				$display = 2;
			}
			//We load the array.
			$xml = simplexml_load_file($file);
			make_config();
			break;

		//The BOSH settings
		#TODO: Check if bosh settings are right and whether open Bosh (e.g. connect to random xmpp); when bosh closed warn the user
		}case 3: {
			include_once("../system/Moxl/loader.php");
			#ToDo: Test Connection
			$Session = array();


				//Apennd it to the error array
				#$errors[] = $e->getMessage();
				//The apge is displayed again:
				#$display = 3;

			if(True){
				$_POST['boshOpen'] = True;
			}
			//We load the array
			$xml = simplexml_load_file($file);
			make_config();
			break;

		#TODO: If BOSH closed, display xmpp form, else display 5
		}case 4: {
			$display = 5;
			break;

		#TOTO: Write Database; Rename conf.xml.part
		}case 5: {
			break;
		//If the user goes back to the checks:
		}case -1: {
			$display = 0;
			break;

		}default: die("Something went wrong");
	}
}else{
	$display = 0;
}

require('template.php');

?>
