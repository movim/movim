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
                    <legend><?php echo $steps[$display]; ?></legend><br />
                        <input type="hidden" name="step" value="4" />
                        <div class="element" <?php //echo generate_Tooltip(t("Enter the XMPP-Server here")); ?>>
                            <label for="xmppServer"><?php echo t("XMPP-Server"); ?></label>
                            <input type="text" id="xmppServer" name="xmppServer" value="<? echo get_preset_value('xmppServer', ''); ?>"/>
                        </div>
                </fieldset>
                <?php include('buttons.php'); ?>
                <br />
            </form>
        </div>
    </div>
</div>
