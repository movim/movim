<?php /* -*- mode: html -*- */
?><!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="content-type" content="text/html; charset=utf-8" />
            <?php
            $this->addCss('css/animations.css');
            $this->addCss('css/style.css');
            $this->addCss('css/forms.css');
            $this->addCss('css/fonts.css');

            $this->addScript('movim_hash.js');
            $this->addScript('movim_utils.js');
            $this->addScript('movim_base.js');
            $this->addScript('movim_tpl.js');
            $this->addScript('movim_rpc.js');
            $this->scripts();
        ?>
        <title><?php echo t('Visio-conference');?></title>
    </head>
    <body>
        <?php $this->widget('Visio', false);?>

        <script type="text/javascript">
            movim_onload();
        </script>
    </body>
</html>
