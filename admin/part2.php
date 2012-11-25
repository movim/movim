<div id="main">
    <div id="left">
        <?php include("error.php"); ?>
        <div class="message warning">
            <p><?php echo t('Move your mousepointer over the configure options to receive more information.');?></p>
        </div>
        </div>
    <div id="center">
        <h1><?php echo $title; ?></h1>
        <div style="margin: 20px;">
            <p <?php //echo generate_Tooltip(t("If no database system is supported then you have to install one or more PHP-plugins and maybe also a database system. For more help on databases see the wiki")) ?>>
                <?php echo t('Movim\'s database engine "Datajar" can handle a lot of database systems. Here you can see which ones are available in your setup.');?><br>
            </p>
            <br />
            <p>
                <?php
                    if(!$djloaded){
                        load_datajar();
                        $djloaded = True;
                    }
                    $dbsystems = datajar_test_backends();
                    foreach($dbsystems as $dbsystem => $supported):
                ?>
                <div class="<?php is_valid($supported); ?>">
                        <?php
                            echo $dbsystem;
                            $dbpreset = $dbsystem
                         ?>
                </div>
                <?PHP endforeach; ?>
            </p>
            <br />
                <form method="post" action="index.php">
                    <fieldset>
                        <legend><?php echo $steps[$display]; ?></legend>
                        <br />

                            <input type="hidden" name="step" value="2" />

                            <div class="element" <?php //echo generate_Tooltip(t("Set the logging options.")); ?>>
                                <label for="dbtype"><?php echo t('Database System to use:'); ?></label>
                                <div class="select">
                                    <select id="dbtype" name="dbtype" onchange="changeDB(this);">		
                                        <?php 
                                            foreach($dbsystems as $dbsystem => $supported):
                                                if($supported):
                                        ?>
                                                <option value="<?php echo $dbsystem; ?>" 
                                                    <?php if(get_preset_value_db('type', $dbpreset) == $dbsystem):?>
                                                        selected="selected"
                                                    <?php endif;?>>
                                                    <?php echo $dbsystem; ?>
                                                </option>
                                                <?php endif; ?>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                    </fieldset>
                    <fieldset id="mysql" style=" display: <?php if(get_preset_value_db('type', $dbpreset) == 'mysql'): echo 'block'; else: echo 'none'; endif;?>">
                            <legend><?php echo t('MySQL Settings'); ?></legend>
                            <div class="element" <?php //echo generate_Tooltip(t("This defaults in most cases to localhost")); ?>>
                              <label for="dbhost"><?php echo t('MySQL Host'); ?></label>
                              <input type="text" name="dbhost" id="dbhost" value="<?  echo get_preset_value_db('host', 'localhost'); ?>" />
                            </div>
                            <div class="element" <?php //echo generate_Tooltip(t("If not sure, leave default")); ?>>
                              <label for="dbport"><?php echo t('MySQL Port'); ?></label>
                              <input type="text" name="dbport" id="dbport" value="<?  echo get_preset_value_db('port', get_mysql_port()); ?>" />
                            </div>
                            <div class="element" <?php //echo generate_Tooltip(t("You get this values from your hosting provider or you have to create a new MySQL user on your system")); ?>>
                              <label for="dbusername"><?php echo t('MySQL Username'); ?></label>
                              <input type="text" name="dbusername" id="dbusername" value="<?  echo get_preset_value_db('username', ''); ?>" />
                            </div>
                            <div class="element" <?php //echo generate_Tooltip(t("You get this values from your hosting provider or you have to create a new MySQL user on your system")); ?>>
                              <label for="dbpassword"><?php echo t('MySQL Password'); ?></label>
                              <input type="password" name="dbpassword" id="dbpassword" value="<?  echo get_preset_value_db('password', ''); ?>" />
                            </div>
                            <div class="element" <?php //echo generate_Tooltip(t("You get this values from your hosting provider or you have to create a new MySQL database on your system")); ?>>
                              <label for="dbdatabase"><?php echo t('MySQL Database'); ?></label>
                              <input type="text" name="dbdatabase" id="dbdatabase" value="<?  echo get_preset_value_db('database', ''); ?>" />
                            </div>
                    </fieldset>
                    <?php /*<fieldset id="sqlite" style=" display: <?php if(get_preset_value_db('type', $dbpreset) == 'sqlite'): echo 'block'; else: echo 'none'; endif;?>">
                        <legend><?php echo t('SQlite Settings'); ?></legend>
                            <div class="element" <?php echo generate_Tooltip(t("Enter a full path! Webserver must have read/write access")); ?>>
                              <label for="dbdatabase"><?php echo t('Path to SQlite file'); ?></label>
                              <input type="text" name="dbdatabase" id="dbdatabase" value="<?  echo get_preset_value_db('database', '/dev/null'); ?>" />
                            </div>
                    </fieldset>*/ ?>
                    <?php include('buttons.php'); ?>
                    <br />
                </form>
            <div class="clear"></div>
        </div>
    </div>
</div>
