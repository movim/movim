<?php

require_once('system/Lang/languages.php');

function test_dir($dir)
{
  return (file_exists($dir) && is_dir($dir) && is_writable($dir));
}

function test_requirements()
{
  $errors = array();

  $v = explode('.', phpversion());
  if($v[0] < 5 || ($v[0] == 5 && $v[1] < 3)) {
    $errors[] = t("PHP version mismatch. Movim requires PHP 5.3 minimum.");
  }
  if(!extension_loaded('curl')) {
    $errors[] = t("Movim requires the %s extension.", 'PHP Curl');
  }
  if(!extension_loaded('SimpleXML')) {
    $errors[] = t("Movim requires the %s extension.", 'SimpleXML');
  }
  if(!test_dir('./')) {
    $errors[] = t("Movim's folder must be writable.");
  }
  /*if(!test_dir('user')) {
    $errors[] = t("The <em>%s</em> folder must exist and be writable.", 'user');
  }
  if(!test_dir('log')) {
    $errors[] = t("The <em>%s</em> folder must exist and be writable.", 'log');
  }*/

  return (count($errors) > 0)? $errors : false;
}

function make_field($name, $label, $input)
{
  ?>
  <div class="field">
    <label for="<?php echo $name;?>"><?php echo $label;?></label>
    <div class="field-input">
      <?php echo $input;?>
    </div>
  </div>
  <?php
}

function make_select($name, $title, array $options, $default = null) {
  $opts = "<select name=\"$name\">";
  foreach($options as $name => $val) {
    $selected = '';
    if($default !== null && $default == $name) {
      $selected = 'selected="selected" ';
    }
    $opts.= '<option '.$selected.'value="'.$name.'">'.$val."</option>\n";
  }
  $opts.= "</select>\n";

  make_field($name, $title, $opts);
}

function make_checkbox($name, $title, $value)
{
  $checked = "";
  if($value) {
    $checked = 'checked="checked" ';
  }
  make_field($name, $title, '<input type="checkbox" '.$checked.'name="'.$name.'" />');
}

function make_textbox($name, $title, $value)
{
  make_field($name, $title, '<input type="text" name="'.$name.'" value="'.$value.'"/>');
}

function make_button($name, $label)
{
  make_field($name, '&nbsp;', '<input type="submit" name="'.$name.'" value="'.$label.'" />');
}

function list_themes()
{
  $dir = opendir('themes');
  $themes = array();

  while($theme = readdir($dir)) {
    if(preg_match('/^\.+$/', $theme)
       || !is_dir('themes/'.$theme)) {
      continue;
    }

    $themes[$theme] = $theme;
  }

  return $themes;
}

function list_lang()
{
  $dir = opendir('i18n');
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

function show_install_form()
{
  ?>
  <h1><?php echo t('Movim Installer'); ?></h1>
  <form method="post">
    <input type="hidden" name="install" value="true" />
    <?php
    make_select('theme', t("Theme"), list_themes());
    make_select('language', t("Default language"), list_lang());
    make_textbox('boshCookieTTL', t("Bosh cookie's expiration (s)"), 3600);
    make_textbox('boshCookiePath', t("Bosh cookie's path"), '/');
    make_checkbox('boshCookieDomain', t("Bosh cookie's domain"), false);
    make_checkbox('boshCookieHTTPS', t("Use HTTPS for Bosh"), false);
    make_checkbox('boshCookieHTTPOnly', t("Use only HTTP for Bosh"), true);
    make_select('verbosity', t("Log verbosity"), array('empty', 'terse', 'normal', 'talkative', 'ultimate'), 4);
    make_checkbox('accountCreation', t("Allow account creation"), false);
    echo '<hr />';
    echo '<h2>Default Bosh server settings</h2>'.PHP_EOL;
    make_textbox('defBoshHost', t("Bosh server"), 'natsu.upyum.com');
    make_textbox('defBoshSuffix', t("Bosh suffix"), 'http-bind');
    make_textbox('defBoshPort', t("Bosh Port"), '80');
    echo '<hr />';
    echo t('<h2>Storage</h2>') . PHP_EOL;
/*    make_select('storage', t("Storage driver"), array('sqlite'));
      make_textbox('database', t("Database"), 'movim.sqlite');*/
    make_button('send', 'Install');
    ?>
  </form>
  <?php
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

function get_checkbox($name, $if = 'true', $else = 'false')
{
  return (isset($_POST[$name])? $if : $else);
}

function perform_install()
{
  // Creating the folders.
  if(!test_dir('user') && !@mkdir('user')) {
    echo t("Couldn't create directory '%s'.", 'user');
    return false;
  }
  if(!test_dir('log') && !@mkdir('log')) {
    echo t("Couldn't create directory '%s'.", 'log');
    return false;
  }
  if(!test_dir('config') && !@mkdir('config')) {
    echo t("Couldn't create directory '%s'.", 'config');
    return false;
  }

  // Creating the configuration file.
  $conf = array(
    'config' => array(
      'theme' => $_POST['theme'],
      'defLang' => $_POST['language'],
      'boshCookieTTL' => $_POST['boshCookieTTL'],
      'boshCookiePath' => $_POST['boshCookiePath'],
      'boshCookieDomain' => get_checkbox('boshCookieDomain'),
      'boshCookieHTTPS' => get_checkbox('boshCookieHTTPS'),
      'boshCookieHTTPOnly' => get_checkbox('boshCookieHTTPOnly'),
      'logLevel' => $_POST['verbosity'],
      'accountCreation' => get_checkbox('accountCreation', 1, 0),
      'defBoshHost' => $_POST['defBoshHost'],
      'defBoshSuffix' => $_POST['defBoshSuffix'],
      'defBoshPort' => $_POST['defBoshPort'],
/*      'storageDriver' => $_POST['storage'],
        'database' => $_POST['database'],*/
      ),
    );
  if(!@file_put_contents('config/conf.xml', make_xml($conf))) {
    echo t("Couldn't create configuration file '%s'.", 'config/conf.xml');
    return false;
  }

  return true;
}

?><!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<title>MOVIM</title>
		<link rel="shortcut icon" href="themes/movim/img/favicon.ico" />
		<link rel="stylesheet" href="themes/movim/css/style2.css" type="text/css" />
	</head>
	<body>
		<div id="content">
      <?php

      $errors = test_requirements();
      if($errors) {
        // Ah ah, there are some errors.
        ?>
			  <h1><?php echo t('Compatibility Test'); ?></h1>
        <p class="center"><?php echo t('The following requirements were not met. Please make sure they are all satisfied in order to install Movim.'); ?></p>
        <?php
        foreach($errors as $error) {
          ?>
          <p class="error"><?php echo $error;?></p>
          <?php
        }
      } else {
        // Doing the job
        if(isset($_POST['install'])) {
          // Installing.
          if(perform_install()) {
            ?>
            <h1><?php echo t('Movim is installed!');?></h1>
            <p><?php echo t('You can now access your shiny %sMovim instance%s',
                            '<a href="index.php">',
                            '</a>');?></p>
            <?php
          } else {
            show_install_form();
          }
        } else {
          // Install form.
          show_install_form();
        }
      }

      ?>
		</div>
	</body>

</html>

