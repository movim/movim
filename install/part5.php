<div id="left" style="width: 230px; padding-top: 10px;">
	<div class="message warning" id="leftside">
		<p><?php echo t('Move your mousepointer over the configure options to receive more information.');?></p>
	</div>
</div>
<div id="center" style="padding: 20px;" >
	<h1 style="padding: 0px;"><?php echo $title; ?></h1><br />
	<?php echo t("You are able to access this installation manager later to make changes to your install. Please specify a username and a password to protect it."); ?>
    <br /><br />
    <form method="post" action="index.php">
        <fieldset>
            <legend><?php echo $steps[$display]; ?></legend><br />
                <input type="hidden" name="step" value="5" />
                <div class="element" <?php echo generate_Tooltip(t("Enter a us")); ?>>
                    <label for="defBoshURL"><?php echo t("Bosh URL"); ?></label>
                    <input type="text" id="defBoshURL" name="defBoshURL" value="<? echo get_preset_value('defBoshURL', ''); ?>"/>

                </div>
                <div class="element" <?php echo generate_Tooltip(t("Enter here the BOSH-URL in the form: http://domain:123/asd")); ?>>
                    <label for="defBoshURL"><?php echo t("Bosh URL"); ?></label>
                    <input type="text" id="defBoshURL" name="defBoshURL" value="<? echo get_preset_value('defBoshURL', ''); ?>"/>

                </div>			                
        </fieldset>
        <fieldset id="dbform">
            
        </fieldset>
        <?php include('buttons.php'); ?>
        <br />
    </form>
</div>

