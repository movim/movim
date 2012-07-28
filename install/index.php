<?php
require_once('../system/Lang/i18n.php');
require_once('../system/Lang/languages.php');

error_reporting(E_ALL);
ini_set('display_errors', '1');

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

function test_dir($dir)
{
  return (file_exists($dir) && is_dir($dir) && is_writable($dir));
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

function test_requirements()
{
  $errors = array();

  if(!(version_compare(PHP_VERSION, '5.3.0') >= 0)) {
    $errors[] = t("PHP version mismatch. Movim requires PHP 5.3 minimum.")." ".t("Actual version : "). PHP_VERSION .
                '<div class="guidance">'.t("Update your PHP version or contact your server administrator").'</div>';
  }
  if(!extension_loaded('curl')) {
    $errors[] = t("Movim requires the %s extension.", 'PHP Curl') .
                '<div class="guidance">'.t("Install %s and %s packages", 'php5-curl', 'curl').'</div>';
  }
  if(!extension_loaded('gd')) {
    $errors[] = t("Movim requires the %s extension.", 'PHP GD') .
                '<div class="guidance">'.t("Install the %s package", 'php5-gd').'</div>';
  }
  if(!extension_loaded('SimpleXML')) {
    $errors[] = t("Movim requires the %s extension.", 'SimpleXML') .
                '<div class="guidance">'.t("Install the %s package", 'php5-cli').'</div>';
  }
  if(!test_dir('../')) {
    $errors[] = t("Movim's folder must be writable.") .
                '<div class="guidance">'.t("Enable read and write rights on Movim's root folder").'</div>';
  }
  /*if(!test_dir('user')) {
    $errors[] = t("The <em>%s</em> folder must exist and be writable.", 'user');
  }
  if(!test_dir('log')) {
    $errors[] = t("The <em>%s</em> folder must exist and be writable.", 'log');
  }*/

  // Must have sqlite or mysql (so far...)
  if(!extension_loaded('mysql') && !class_exists('SQLite3')) {
      $exts = array('MySQL', 'SQLite');
      $exts_txt = implode(t("or"), $exts);
      $errors[] = t("Movim requires the %s extension.", $exts_txt);
  }

  global $databases;
  if(extension_loaded('mysql'))
      $databases['mysql'] = 'MySQL';
  if(class_exists('SQLite3'))
      $databases['sqlite'] = 'SQLite';

  return (count($errors) > 0)? $errors : false;
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

function perform_install()
{
  // Creating the folders.
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

  // Creating the configuration file.
  $conf = array(
    'config' => array(
      'theme'              => $_POST['theme'],
      'defLang'            => $_POST['language'],
      'boshCookieTTL'      => $_POST['boshCookieTTL'],
      'boshCookiePath'     => $_POST['boshCookiePath'],
      'boshCookieDomain'   => get_checkbox('boshCookieDomain'),
      'boshCookieHTTPS'    => get_checkbox('boshCookieHTTPS'),
      'boshCookieHTTPOnly' => get_checkbox('boshCookieHTTPOnly'),
      'logLevel'           => $_POST['verbosity'],
      //Temporary workaround
      'accountCreation'    => 1,
      'host'               => $_POST['host'],
      'domain'             => $_POST['domain'],
      'defBoshHost'        => $_POST['defBoshHost'],
      'defBoshSuffix'      => $_POST['defBoshSuffix'],
      'defBoshPort'        => $_POST['defBoshPort'],
      'storageDriver'      => $_POST['datajar'],
      'storageConnection'  => $_POST['database'],
      'proxyEnabled'       => get_checkbox('proxyEnabled'),
      'proxyURL'           => $_POST['proxyURL'],
      'proxyPort'          => $_POST['proxyPort'],
      'maxUsers'           => $_POST['maxUsers'],
      ),
    );
  if(!@file_put_contents('../config/conf.xml', make_xml($conf))) {
    echo t("Couldn't create configuration file '%s'.", 'config/conf.xml');
    return false;
  }

  return true;
}

$step = 'part1.php';

if(isset($_POST['install'])) {
    // We test the Bosh configuration
    if(!test_bosh($_POST['defBoshHost'], $_POST['defBoshPort'], $_POST['defBoshSuffix'], $_POST['host'])) {
        goto loadpage;
    }

	// We create the configuration file
    perform_install();

    // We try to connect to the database
    try {
        include('../init.php');
    } catch (Exception $e) {
		set_error('bdd', t("Database connection failed with error '%s'", $e->getMessage()));
        goto loadpage;
    }

    // We create correctly the tables
    global $sdb;
    $contact = new Contact();
    $sdb->create($contact);

    $conf = new ConfVar();
    $sdb->create($conf);

    $message = new Message();
    $sdb->create($message);

    $presence = new Presence();
    $sdb->create($presence);

    $post = new Post();
    $sdb->create($post);

    $caps = new Caps();
    $sdb->create($caps);

    $attachment = new Attachment();
    $sdb->create($attachment);

	$step = 'part2.php';
}

loadpage:
require($step);

?>
