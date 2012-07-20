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
		<div id="content" style="width: 900px">
            <div id="left" style="width: 230px; padding-top: 10px;">
                <div class="warning">
                    <p><?php echo t('Thank you for downloading Movim!');?></p>
                    <p><?php echo t('Before you enjoy your social network, a few adjustements are required.'); ?></p>
                    <p><?php echo t('Keep in mind that Movim is still under development and will handle many personal details. Its use can potentially endanger your data. Always pay attention to information that you submit.'); ?></p>
                </div>
            </div>
            <div id="center" style="padding: 20px;" >
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
              if(has_errors()):?>
              <div class="error">
                  <?php echo t('Some errors were detected. Please correct them for the installation to proceed.');?>
              </div>
              <?php endif;?>

              <h1 style="padding: 10px 0px;"><?php echo t('Movim Installer'); ?></h1>
              <br />
                <form method="post" action="index.php">
                    <fieldset>
                        <legend>General</legend>
                        <p>
                            <input type="hidden" name="install" value="true" />
                            <label for="movim">Theme</label>
                            <select id="theme" name="theme">
                                <?php
                                    foreach(list_themes() as $key=>$value)
                                        echo '<option value="'.$key.'"'.(($_POST['theme'] == $value)? ' selected="selected"': '').'>'.$value.'</option>';
                                ?>
                            </select>
                        </p>

                        <p>
                            <label for="da">Default language</label>
                            <select id="language" name="language">
                                <?php
                                    foreach(list_lang() as $key=>$value)
                                        echo '<option value="'.$key.'"'.(($_POST['theme'] == $value)? ' selected="selected"': '').'>'.$value.'</option>';
                                ?>

                            </select>
                        </p>

                        <p>
                            <label for="boshCookieTTL">Bosh cookie's expiration (s)</label>
                            <input type="text" id="boshCookieTTL" name="boshCookieTTL" value="<?php echo (isset($_POST['boshCookieTTL'])? $_POST['boshCookieTTL'] : 3600);?>"/>
                        </p>

                        <p>
                            <label for="boshCookiePath">Bosh cookie's path</label>
                            <input type="text" id="boshCookiePath" name="boshCookiePath" value="<?php echo (isset($_POST['boshCookiePath'])? $_POST['boshCookiePath'] : '/');?>"/>
                        </p>

                       <p>
                            <label for="boshCookieDomain">Bosh cookie's domain</label>
                            <input type="checkbox" name="boshCookieDomain" id="boshCookieDomain" <?php if(isset($_POST['boshCookieFomain'])) echo 'checked="checked"';?>/>
                        </p>

                        <p>
                            <label for="boshCookieHTTPS">Use HTTPS for Bosh</label>
                            <input type="checkbox" name="boshCookieHTTPS" id="boshCookieHTTPS" <?php if(isset($_POST['boshCookieHTTPS'])) echo 'checked="checked"';?>/>
                        </p>

                        <p>
                            <label for="boshCookieHTTPOnly">Use only HTTP for Bosh</label>
                            <input type="checkbox" checked="checked" name="boshCookieHTTPOnly" id="boshCookieHTTPOnly" <?php if(isset($_POST['boshCookieHTTPOnly'])) echo 'checked="checked"';?>/>
                        </p>

    <?php
    $logopts = array(
        0 => t('empty'),
        2 => t('terse'),
        4 => t('normal'),
        6 => t('talkative'),
        7 => t('ultimate'),
        );
    $default_log = 4;
    ?>
                        <p>
                            <label for="7">Log verbosity</label>
                            <select id="verbosity" name="verbosity">
                                <?php foreach($logopts as $lognum => $text):?>
                                    <option value="<?php echo $lognum;?>"
                                    <?php if(isset($_POST['verbosity'])):?>
                                        <?php if($_POST['verbosity'] == $lognum):?>
                                            selected="selected"
                                        <?php endif;?>
                                    <?php elseif($lognum == $default_log):?>
                                            selected="selected"
                                    <?php endif;?>>
                                        <?php echo $text;?>
                                    </option>
                                <?php endforeach;?>
                            </select>
                        </p>

                        <p>
                            <label for="accountCreation">Allow account creation</label>
                            <input type="checkbox" name="accountCreation" id="accountCreation" <?php if(!isset($_POST['accountCreation']) || !$_POST['accountCreation']) echo 'checked="checked"';?>/>
                        </p>
                    </fieldset>

                    </br>
                    <fieldset>
                        <legend><?php echo t('XMPP Connection Preferences'); ?></legend>
                        <p>
                            <label for="host">XMPP Host</label>
                            <input type="text" id="host" name="host" value="<?php echo (isset($_POST['host'])? $_POST['host'] : 'movim.eu');?>"/>
                        </p>
                        <p>
                            <label for="domain">XMPP Domain</label>
                            <input type="text" id="domain" name="domain" value="<?php echo (isset($_POST['domain'])? $_POST['domain'] : 'etenil.thruhere.net');?>"/>
                        </p>
                    </fieldset>

                    </br>
                    <fieldset>
                        <legend><?php echo t('BOSH Connection Preferences'); ?></legend>

                        <?php if(err('bosh')) { ?>
                            <div class="error">
                            <?php echo err('bosh'); ?>
                            </div>
                        <?php } ?>

                        <p>
                            <label for="defBoshHost">Bosh Host</label>
                            <input type="text" id="defBoshHost" name="defBoshHost" value="<?php echo (isset($_POST['defBoshHost'])? $_POST['defBoshHost'] : 'bosh.etenil.thruhere.net');?>"/>
                        </p>

                        <p>
                            <label for="defBoshSuffix">Bosh Suffix</label>
                            <input type="text" id="defBoshSuffix" name="defBoshSuffix" value="<?php echo (isset($_POST['defBoshSuffix'])? $_POST['defBoshSuffix'] : '');?>"/>
                        </p>

                        <p>
                            <label for="defBoshPort">Bosh Port</label>
                            <input style="width: 50px" type="text" id="defBoshPort" name="defBoshPort" value="<?php echo (isset($_POST['defBoshPort'])? $_POST['defBoshPort'] : '80');?>"/>
                        </p>
                    </fieldset>

                    <br />
                    <fieldset>
                        <legend><?php echo t('Proxy Preferences'); ?></legend>

                        <p>
                            <label for="proxyEnabled">Proxy server enabled</label>
                            <input type="checkbox" name="proxyEnabled" id="proxyEnabled" <?php if(isset($_POST['proxyEnabled'])) echo 'checked="checked"';?>/>
                        </p>

                        <p>
                            <label for="proxyURL">Proxy URL (without http(s)://)</label>
                            <input style="width: 300px" type="text" id="proxyURL" name="proxyURL" value="<?php echo (isset($_POST['proxyURL'])? $_POST['proxyURL'] : '');?>"/>
                        </p>

                        <p>
                            <label for="proxyPort">Proxy Port</label>
                            <input style="width: 50px" type="text" id="proxyPort" name="proxyPort" value="<?php echo (isset($_POST['proxyPort'])? $_POST['proxyPort'] : '');?>"/>
                        </p>
                    </fieldset>

                    <br />
                    <fieldset>
                        <legend>Datajar</legend>

                        <?php if(err('bdd')) { ?>

                        <div class="error">
                        <?php echo err('bdd'); ?>
                        </div>

                        <?php } ?>

                        <p>
                            <label for="sqlite">Datajar driver</label>
                            <select id="datajar" name="datajar" onchange="changeDB(this.options[this.selectedIndex].value)">
                                <option value="mysql">MySQL</option>
                                <!--<option value="sqlite">SQLite</option>-->
                            </select>
                        </p>

                        <p>
                            <label for="database">Database</label>
                            <input style="width: 400px" type="text" id="database" name="database" value="<?php echo (isset($_POST['database'])? $_POST['database'] : 'mysql://username:password@host:'.get_mysql_port().'/database');?>"/>
                        </p>

                    </fieldset>

                    <p style="padding: 20px 0px;">
                        <label for="send">&nbsp;</label>
                        <input type="submit" style="float: right" class="button icon submit" id="send" name="send" value="Install" />
                    </p>
                    <br />
                </form>

              <?php }
              ?>
            </div>
		</div>
	</body>

</html>

