<?php /* -*- mode: html -*- */
    $cd = new \Modl\ConfigDAO();
    $config = $cd->get();
?><!DOCTYPE html>
<html ng-app="roster">
  <head>
    <meta charset="utf-8" />
    <title><?php $this->title();?></title>

    <meta name="description" content="<?php echo $config->description; ?>" />
    <meta name="theme-color" content="#1C1D5B" />

    <?php $this->meta();?>

    <link rel="shortcut icon" href="<?php $this->linkFile('img/favicon.ico');?>" />
    <script src="<?php echo BASE_URI; ?>app/assets/js/favico.js"></script>

    <meta name="viewport" content="width=device-width, user-scalable=no">

    <?php
        $this->addCss('style.css');
        $this->addCss('list.css');
        $this->addCss('grid.css');
        $this->addCss('article.css');
        $this->addCss('form.css');
        $this->addCss('color.css');
        $this->addCss('block.css');
        $this->addCss('menu.css');
        $this->addCss('fonts.css');
        $this->addCss('material-design-iconic-font.min.css');

        $this->widget('System');

        $this->scripts();
    ?>
    </head>
    <body>
        <noscript>
            <style type="text/css">
                nav {display:none;} #content {display: none;}
            </style>
            <div class="warning" style="width: 500px; margin: 0 auto;">
            <?php echo __('global.no_js'); ?>
            </div>
        </noscript>
        <div id="hiddendiv"></div>
        <div id="snackbar" class="snackbar"></div>
        <?php $this->widget('Dialog');?>
        <?php $this->widget('Notification');?>
        <?php $this->content();?>
        <script type="text/javascript">movim_onload();</script>
    </body>
</html>
