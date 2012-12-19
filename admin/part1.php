<div id="main">
    <div id="left">
        <div class="message warning">
            <p><?php echo t('Move your mousepointer over the configure options to receive more information.');?></p>
        </div>
    </div>
    <div id="center">
        <h1><?php echo $title; ?></h1>
        <br />
        <div style="margin: 20px;">
            <form method="post" action="index.php">
                <fieldset>
                    <legend><?php echo $steps[$display]; ?></legend>
                        <input type="hidden" name="step" value="1" />

                        
                        <div class="element" <?php //echo generate_Tooltip("So far there is only one theme. :-)"); ?>>
                            <label for="movim" ><?php echo t('Theme'); ?></label>
                                <div class="select">
                                    <select id="theme" name="theme">
                                        <?php
                                            foreach(list_themes() as $key=>$value)
                                            echo '<option value="'.$key.'"'.((get_preset_value('theme', 'movim') == $value)? ' selected="selected"': '').'>'.$value.'</option>';
                                        ?>
                                    </select>
                                </div>
                        </div>
        
                        <div class="element" <?php //echo generate_Tooltip(t("Movim is already tranlsated in a lot of languages. <br>Some translations are unfortunately incomplete you could help translating on launchpad")); ?>>
                            <label for="da" ><?php echo t('Default language'); ?></label>
                            <div class="select">
                                <select id="defLang" name="defLang">
                                    <?php
                                        foreach(list_lang() as $key=>$value)
                                            echo '<option value="'.$key.'"'.((get_preset_value('defLang', 'English') == $key)? ' selected="selected"': '').'>'.$value.'</option>';
                                    ?>
                    
                                </select>
                            </div>
                        </div>
                        
                        <div class="element" <?php //echo generate_Tooltip(t("Movim can limit the maximum allowed amounts of registered users. If you do not want this feature leave the value \'-1\'.")); ?>>
                          <label for="maxUsers"><?php echo t('Maximum population'); ?></label>
                          <input type="text" name="maxUsers" id="maxUsers" value="<?  echo get_preset_value('maxUsers', -1); ?>" />
                        </div>
                        
                        <div class="element" <?php //echo generate_Tooltip(t("Set the logging options.")); ?>>
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
        
                            <label for="7"><?php echo t("Log verbosity"); ?></label>
                            <div class="select">
                                <select id="logLevel" name="logLevel">
                                    <?php foreach($logopts as $lognum => $text):?>
                                        <option value="<?php echo $lognum;?>"
                                            <?php if(get_preset_value('logLevel', $default_log) == $lognum):?>
                                                selected="selected"
                                            <?php endif;?>>

                                            <?php echo $text;?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                </fieldset>
                <?php include('buttons.php'); ?>

            </form>
            <div class="clear"></div>
        </div>
    </div>
</div>
