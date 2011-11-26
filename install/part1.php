<?php
require_once('../system/Lang/i18n.php');
require_once('../system/Lang/languages.php');

function get_mysql_port() { 
    $port = ini_get('mysql.default_port');
    if($port == "")
        $port = ini_get('mysqli.default_port');
    if($port == "")
        $port = 3306;
    return $port;
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

?>

<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<title>MOVIM</title>
		<link rel="shortcut icon" href="../themes/movim/img/favicon.ico" />
		<link rel="stylesheet" href="../themes/movim/css/style2.css" type="text/css" />
        <script type="text/javascript">
          function changeDB(type)
          {
            var dbspec = document.getElementById("database");
            switch(type) {
            case "sqlite":
              dbspec.value = "sqlite:///movim.db";
              break;
            case "mysql":
              dbspec.value = "mysql://username:password@host:<?php echo get_mysql_port(); ?>/database";
              break;
            default:
              dbspec.value = "db://username:password@host:<?php echo get_mysql_port();?>port/database";
            }
          }
        </script>
	</head>
	<body>
		<div id="content">
          <div class="warning right">
            <p><?php echo('Thank you for downloading Movim ! But before you have fun with it, a few adjustements are needed.'); ?></p>
            <p><?php echo('Keep in mind that Movim is still under development and will handle many personal details. Its use can potentially endanger your data. Always pay attention to information that you submit.'); ?></p>
          </div>
          <?php
          $errors = test_requirements();

          if($errors) {
            // Ah ah, there are some errors.
            ?>
			      <h1><?php echo t('Movim Installer')." - ".t('Compatibility Test'); ?></h1>
            <p class="center"><?php echo t('The following requirements were not met. Please make sure they are all satisfied in order to install Movim.'); ?></p><br />
            <?php
            foreach($errors as $error) {
              ?>
              <p class="error"><?php echo $error;?></p>
              <?php
            }
          } else {
          ?>
          <h1><?php echo t('Movim Installer'); ?></h1>
          <br />
            <form method="post" action="part2.php">
                <input type="hidden" name="install" value="true" />
                <div class="field">
                    <label for="movim">Theme</label>
                    <div class="field-input">
                        <select id="theme" name="theme">
                            <?php
                                foreach(list_themes() as $key=>$value)
                                    echo '<option value="'.$key.'">'.$value.'</option>';
                            ?>
                        </select>
                    </div>
                </div>
                
                <div class="field">
                    <label for="da">Default language</label>
                    <div class="field-input">
                        <select id="language" name="language">
                            <?php
                                foreach(list_lang() as $key=>$value)
                                    echo '<option value="'.$key.'">'.$value.'</option>';
                            ?>

                        </select>
                    </div>
                </div>
                
                <div class="field">
                    <label for="boshCookieTTL">Bosh cookie's expiration (s)</label>
                    <div class="field-input">
                    <input type="text" id="boshCookieTTL" name="boshCookieTTL" value="3600"/>    </div>
                </div>
                
                <div class="field">
                    <label for="boshCookiePath">Bosh cookie's path</label>
                    <div class="field-input">
                    <input type="text" id="boshCookiePath" name="boshCookiePath" value="/"/>    </div>
                </div>
                
                <div class="field">
                    <label for="boshCookieDomain">Bosh cookie's domain</label>
                    <div class="field-input">
                    <input type="checkbox" name="boshCookieDomain" id="boshCookieDomain" />    </div>
                </div>
                
                <br /><div class="field">
                    <label for="boshCookieHTTPS">Use HTTPS for Bosh</label>
                    <div class="field-input">
                    <input type="checkbox" name="boshCookieHTTPS" id="boshCookieHTTPS" />    </div>
                </div>
                
                <br /><div class="field">
                    <label for="boshCookieHTTPOnly">Use only HTTP for Bosh</label>
                    <div class="field-input">
                    <input type="checkbox" checked="checked" name="boshCookieHTTPOnly" id="boshCookieHTTPOnly" />    </div>
                </div>
                
                <br /><div class="field">
                <label for="7">Log verbosity</label>
                    <div class="field-input">
                        <select id="verbosity" name="verbosity"><option value="0">empty</option>
                            <option value="2">terse</option>
                            <option selected="selected" value="4">normal</option>
                            <option value="6">talkative</option>
                            <option value="7">ultimate</option>
                        </select>
                    </div>
                </div>
                
                <div class="field">
                <label for="accountCreation">Allow account creation</label>
                    <div class="field-input">
                        <input type="checkbox" name="accountCreation" id="accountCreation" />    </div>
                    </div>
                
                <br />    
                <hr /><h2>Default Bosh server settings</h2>

                <?php if(isset($_GET['err']) && $_GET['err']=='bosh') { ?>
                
                <div class="error">
                <?php echo t('The Bosh configuration is invalid'); ?>
                </div>
                
                <?php } ?>

                <div class="field">
                <label for="defBoshHost">Bosh Host</label>
                <div class="field-input">
                <input type="text" id="defBoshHost" name="defBoshHost" value="etenil.thruhere.net"/>    </div>
                </div>
                <div class="field">
                <label for="defBoshSuffix">Bosh Suffix</label>

                <div class="field-input">
                <input type="text" id="defBoshSuffix" name="defBoshSuffix" value="http-bind"/>    </div>
                </div>
                
                <div class="field">
                    <label for="defBoshPort">Bosh Port</label>
                    <div class="field-input">
                        <input type="text" id="defBoshPort" name="defBoshPort" value="5280"/>    </div>
                </div>
                <br />    
                    
                <hr /><h2>Storage</h2>
                
                <?php if(isset($_GET['err']) && $_GET['err']=='bdd') { ?>
                
                <div class="error">
                <?php echo t('The Database configuration is invalid'); ?>
                </div>
                
                <?php } ?>
                
                <div class="field">
                    <label for="sqlite">Storage driver</label>
                    <div class="field-input">
                        <select id="storage" name="storage" onchange="changeDB(this.options[this.selectedIndex].value)">
                            <option value="mysql">MySQL</option>
                            <!--<option value="sqlite">SQLite</option>-->
                        </select>
                    </div>
                </div>
                
                <div class="field">
                    <label for="database">Database</label>
                    <div class="field-input">
                    <input style="width: 400px" type="text" id="database" name="database" value="mysql://username:password@host:<?php echo get_mysql_port(); ?>/database"/>    </div>
                </div>

                <div class="field">
                    <label for="send">&nbsp;</label>
                    <div class="field-input">
                    <input type="submit" id="send" name="send" value="Install" />    </div>
                </div>
            </form>
          
          <?php }
          ?>
		</div>
	</body>

</html>

