<div id="main">
    <div id="left">
        <div class="message warning">
            <p><?php echo t('Move your mousepointer over the configure options to receive more information.');?></p>
        </div>
    </div>
    <div id="center">
        <h1><?php echo $title; ?></h1>
        <div style="margin: 20px;">
            <?php echo t("That's it. Your configuration is now finished, and the database has been created."); ?><br />
            <?php echo t("If you want to change something later on you can do this by coming back to the installer."); ?>
            
            <a 
                class="button icon follow" 
                href="../"
                style="float:right; margin-top: 2em;">
                <?php echo t('Here we go !'); ?>
            </a>
        </div>        
    </div>
</div>
