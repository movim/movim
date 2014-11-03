<?php /* -*- mode: html -*- */
?><!DOCTYPE html>
<html>
    <head>
        <meta name="viewport" content="width=device-width, user-scalable=no";>
        <meta http-equiv="content-type" content="text/html; charset=utf-8" />
        <?php
            $this->addCss('css/animations.css');
            $this->addCss('css/forms.css');
            $this->addCss('css/fonts.css');
            $this->addCss('css/style.css');

            $this->widget('System');

            $this->addScript('movim_hash.js');
            $this->addScript('movim_utils.js');
            $this->addScript('movim_base.js');
            $this->addScript('movim_tpl.js');
            $this->addScript('movim_websocket.js');
            $this->scripts();

            $this->addCss('css/font-awesome.css'); 
        ?>
        <title><?php echo __('page.visio');?></title>
    </head>
    <body>
        <?php $this->widget('Visio', false);?>

        <script type="text/javascript">
            movim_onload();
        </script>
    </body>
</html>
