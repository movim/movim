<div id="main">
    <div id="left">
        <?php include("error.php"); ?>
        <div class="message warning">
            <p><?php echo t('Move your mousepointer over the configure options to receive more information.');?></p>
        </div>
    </div>
    <div id="center">
        <h1 ><?php echo $title; ?></h1>
        <br />
        <div style="margin: 20px;">
        
            <form method="post" action="index.php">
                <fieldset>
                    <legend><?php echo $steps[$display]; ?></legend><br />
                        <input type="hidden" name="step" value="3" />
                        <div class="element" <?php //echo generate_Tooltip(t("Enter here the BOSH-URL in the form: http(s)://domain:port/path<br>If you enter an open BOSH-Server, you can connect to many XMPP-Servers. If it is closed, you have to specify the corresponding Server on the next page.<br>If you are unsure about this config option visit the wiki")); ?>>
                            <label for="boshUrl"><?php echo t("Bosh URL"); ?></label>
                            <input type="text" id="boshUrl" name="boshUrl" value="<? echo get_preset_value('boshUrl', ''); ?> " size="40"/>
                        </div>
                        <!--<div class="element" <?php //echo generate_Tooltip(t("When ticked, users can define their own Bosh-Server at the login form.")); ?>>
                                <label for="userDefinedBosh"><?php echo t('BOSH changeable'); ?></label>
                                <input type="checkbox" name="userDefinedBosh" id="userDefinedBosh" <?php if(get_preset_value('userDefinedBosh', True)){ echo 'checked="checked"'; }?>/>
                        </div>-->
                        <!--<div class="element" <?php //echo generate_Tooltip(t("If you have to connect to the BOSH server with a Proxy fill this form with a proxy URL in the form http://hostname:port otherwise leave blank. <br> If unsure leave blank")); ?>>
                            <label for="boshProxy"><?php echo t("Bosh Proxy"); ?></label>
                            <input type="text" id="boshProxy" name="boshProxy" value="<? echo get_preset_value('boshProxy', ''); ?> " size="40"/>
                        </div>-->
                </fieldset>
                <?php include('buttons.php'); ?>
                <br />
            </form>
        </div>
    </div>
</div>
