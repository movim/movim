<?php /* -*- mode: html -*- */
?><!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="content-type" content="text/html; charset=utf-8" />
        <?php $this->scripts(); ?>
        <title><?php echo t('Chat Box');?></title>
    </head>
    <body>
        <?php $this->widget('ChatPop', false);?>
    </body>
</html>
