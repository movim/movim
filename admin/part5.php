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
            <?php echo t("You are able to access this installation manager later to make changes to your install. Please specify a username and a password to protect it."); ?>
        </p>
        <br />

            <form method="post" action="index.php">
                <fieldset>
                    <legend><?php echo $steps[$display]; ?></legend><br />
                        <input type="hidden" name="step" value="5" />
                        <div class="element" <?php //echo generate_Tooltip(t("Enter a us")); ?>>
                            <label for="username"><?php echo t("Username"); ?></label>
                            <input type="text" id="user" name="user" value="<? echo get_preset_value('user', ''); ?>"/>

                        </div>
                        <div class="element" <?php //echo generate_Tooltip(t("Enter here the BOSH-URL in the form: http://domain:123/asd")); ?>>
                            <label for="pass"><?php echo t("Password"); ?></label>
                            <input type="password" id="pass" name="pass" value=""/>
                        </div>			                
                        <div class="element" <?php //echo generate_Tooltip(t("Enter here the BOSH-URL in the form: http://domain:123/asd")); ?>>
                            <label for="repass"><?php echo t("Retype password"); ?></label>
                            <input type="password" id="repass" name="repass" value=""/>
                        </div>			                
                </fieldset>

                <?php include('buttons.php'); ?>
                <br />
            </form>
        </div>
    </div>
</div>
