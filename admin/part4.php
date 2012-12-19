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
            <p>
                <?php echo t("If you want to specify a list of authorized XMPP servers on your Movim pod and forbid the connection on all the others please put their domain name here, with comma (ex: movim.eu,jabber.fr)"); ?>
            </p>
            <p><?php echo t("Leave this field blank if you allow the access to all the XMPP accounts."); ?></p>
            <br />
                    
            <form method="post" action="index.php">
                <fieldset>
                    <legend><?php echo $steps[$display]; ?></legend><br />
                        <input type="hidden" name="step" value="4" />
                        <div class="element" <?php //echo generate_Tooltip(t("Enter the XMPP-Server here")); ?>>
                            <label for="xmppWhiteList"><?php echo t("List of whitelisted XMPP servers"); ?></label>
                            <input type="text" id="xmppWhiteList" name="xmppWhiteList" value="<? echo get_preset_value('xmppWhiteList', ''); ?>"/>
                        </div>
                </fieldset>
                <?php include('buttons.php'); ?>
                <br />
            </form>
        </div>
    </div>
</div>
