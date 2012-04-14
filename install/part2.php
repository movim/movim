<?php
require_once('../system/Lang/i18n.php');
require_once('../system/Lang/languages.php');

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

    printf($rs['errmsg']);

    curl_close($ch);
    $arr = simplexml_load_string($rs["content"]);
    if(is_object($arr))
        $att = $arr->attributes();
    if(isset($att['sid']))
        return true;
    else
        return false;
}

function test_dir($dir)
{
  return (file_exists($dir) && is_dir($dir) && is_writable($dir));
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
  if(!test_dir('../user') && !@mkdir('../user')) {
    echo t("Couldn't create directory '%s'.", 'user');
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
      'accountCreation'    => get_checkbox('accountCreation', 1, 0),
      'host'               => $_POST['host'],
      'domain'             => $_POST['domain'],
      'defBoshHost'        => $_POST['defBoshHost'],
      'defBoshSuffix'      => $_POST['defBoshSuffix'],
      'defBoshPort'        => $_POST['defBoshPort'],
      'datajarDriver'      => $_POST['datajar'],
      'datajarConnection'  => $_POST['database'],
      'proxyEnabled'       => get_checkbox('proxyEnabled'),
      'proxyURL'           => $_POST['proxyURL'],
      'proxyPort'          => $_POST['proxyPort'],
      ),
    );
  if(!@file_put_contents('../config/conf.xml', make_xml($conf))) {
    echo t("Couldn't create configuration file '%s'.", 'config/conf.xml');
    return false;
  }

  return true;
}

if(isset($_POST['install'])) {
    // We test the Bosh configuration
    if(!test_bosh($_POST['defBoshHost'], $_POST['defBoshPort'], $_POST['defBoshSuffix'], $_POST['host'])) {
        header('Location:part1.php?err=bosh'); exit;
    }
    // We create the configuration file
    perform_install();

    // We try to connect to the database
    try {
        include('../init.php');
    } catch (Exception $e) {
        header('Location:part1.php?err=bdd');
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

    $attachment = new Attachment();
    $sdb->create($attachment);
    
    $post = new Post();
    $sdb->create($post);
}

?>

<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<title>MOVIM</title>
		<link rel="shortcut icon" href="../themes/movim/img/favicon.ico" />
		<link rel="stylesheet" href="../themes/movim/css/style2.css" type="text/css" />
	</head>
	<body>
		<div id="content">
	        <h1><?php echo t('Movim Installer')." - ".t('Success !'); ?></h1>

	        <div class="valid">
                - <?php echo t('Valid Bosh'); ?><br />
                - <?php echo t('Database Detected'); ?><br />
                - <?php echo t('Database Movim schema installed'); ?><br />
            </div>
            <div class="warning">
                <?php echo t('You can now access your shiny Movim instance %sJump In !%s', '<a class="button tiny" style="float: right;" href="../index.php">', '</a>');?><br /><br />
                - <?php echo t('Please remove the %s folder in order to complete the installation', 'install/'); ?>
            </div>
		</div>
    </body>
</html>
