<div id="main">
    <div id="left">
        <div class="message warning">
            <p><?php echo t('Thank you for downloading Movim!');?></p>
            <p><?php echo t('Before you enjoy your social network, a few adjustements are required.'); ?></p>
            <p><?php echo t('Keep in mind that Movim is still under development and will handle many personal details. Its use can potentially endanger your data. Always pay attention to information that you submit.'); ?></p>
            <p><?php echo t('For help on installation see the').' <a href="http://wiki.movim.eu/install">wiki</a>.'?></p>
        </div>
    </div>
    <div id="center">
        <h1><?php echo t('Welcome to Movim!'); ?></h1>
        <div style="margin: 20px">
            <h2><?php echo t('You are about to install or change the configuration of the distributed XMPP-based opensource Social Network Movim.');?></h2>
            
            <!--<div class="message info">
                <?php echo ('This sign <img src="../themes/movim/img/icons/follow_icon.png"> indicates that there are additional help texts available, which will be displayed on the left.'); ?>
            </div>-->
            <br />
            
            <p>
                <?php echo t('Movim requires certain external components. Please install them before you can succeed:');?>
            </p>
            <br />
                
            <div class="<?php is_valid((version_compare(PHP_VERSION, '5.3.0') >= 0)); ?>">
                <?php echo t('Your PHP-Version: %s <br>Required: 5.3.0', PHP_VERSION); ?>
            </div>
            <div class="<?php is_valid(extension_loaded('curl')); ?>">
                <?php echo t('CURL-Library'); ?>
            </div>
            <div class="<?php is_valid(extension_loaded('gd')); ?>">
                <?php echo t('GD'); ?>
            </div>
            <div class="<?php is_valid(extension_loaded('SimpleXml')); ?>">
                <?php echo t('SimpleXML'); ?>
            </div>
            <div class="<?php is_valid(test_dir('../')); ?>">
                <?php echo t('Read and write rights for the webserver in Movim\'s root directory') ?>
            </div>
            <div class="<?php is_valid((datajar_version() >= 0.01)); ?>">
                <?php echo t('<a href="http://datajar.movim.eu">Datajar</a> version: '.datajar_version().'<br> Required: 0.01') ?>
            </div>
            <div class="<?php is_valid(true); ?>">
                <?php echo t('<a href="https://launchpad.net/moxl">Moxl</a> version: asd<br> Required: asdasd') ?>
            </div>

            <?php 
                if($errors) { 
            ?>
                <br />
                <p class="error"><?php echo t('Please make the required changes to continue.'); ?></p>
            <?php 
                } 
            ?>
            
            <br />

            <form method="post" action="index.php" style="float: right;">
                <input type="hidden" name="step" value="0" />
                <?php if(!$errors){ ?>
                    <input 
                        type="submit" 
                        class="button" 
                        id="send" 
                        name="send" 
                        value="<?php echo t('Next'); ?>" />
                <?php  } ?>
            </form>
            <div class="clear"></div>
        </div>
    </div>
</div>
