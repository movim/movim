<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8" />
    <title><?php $this->title(); ?></title>

    <meta name="theme-color" content="#1C1D5B" />
    <?php $this->meta(); ?>
    <meta name="application-name" content="<?php echo APP_TITLE; ?>">
    <link rel="manifest" href="<?php echo \Movim\Route::urlize('manifest'); ?>" />
    <link rel="shortcut icon" href="<?php $this->linkFile('img/favicon.ico');?>" />
    <link rel="icon" type="image/png" href="<?php $this->linkFile('img/app/48.png');?>" sizes="48x48">
    <link rel="icon" type="image/png" href="<?php $this->linkFile('img/app/96.png');?>" sizes="96x96">
    <link rel="icon" type="image/png" href="<?php $this->linkFile('img/app/128.png');?>" sizes="128x128">
    <script src="<?php echo BASE_URI; ?>app/assets/js/favico.js"></script>
    <script src="<?php echo BASE_URI; ?>app/assets/js/pako.js"></script>
    <script src="<?php echo
        \Movim\Route::urlize('system') .
        '&t=' .
        filemtime(CACHE_PATH . 'websocket');
        ?>"></script>

    <meta name="viewport" content="width=device-width, user-scalable=no">

    <?php
        $this->addCSS('style.css');
        $this->addCSS('notification.css');
        $this->addCSS('header.css');
        $this->addCSS('listn.css');
        $this->addCSS('grid.css');
        $this->addCSS('article.css');
        $this->addCSS('form.css');
        $this->addCSS('icon.css');
        $this->addCSS('dialog.css');
        $this->addCSS('card.css');
        $this->addCSS('table.css');
        $this->addCSS('color.css');
        $this->addCSS('block.css');
        $this->addCSS('menu.css');
        $this->addCSS('fonts.css');
        $this->addCSS('title.css');
        $this->addCSS('typo.css');
        $this->addCSS('scrollbar.css');

        $this->scripts();
    ?>
    </head>
    <body dir="<?php $this->dir();?>"
          class="<?php if (!$this->public && \App\User::me()->nightmode) { ?>nightmode<?php } ?>">
        <?php if ($this->js_check) { ?>
        <noscript>
            <style type="text/css">main {display: none;}</style>
            <ul class="list" style="color: white;">
                <li>
                    <p class="center"><?php echo __('global.no_js'); ?></p>
                </li>
            </ul>
        </noscript>
        <?php } ?>
        <div id="hiddendiv"></div>
        <div id="snackbar" class="snackbar"></div>
        <div id="error_websocket" class="snackbar hide" onclick="MovimUtils.reloadThis()">
            <ul class="list">
                <li>
                    <span class="control icon gray">
                        <i class="material-icons">signal_cellular_off</i>
                    </span>
                    <p class="normal">
                        <?php echo __('error.websocket'); ?>
                    </p>
                </li>
            </ul>
        </div>
        <?php $this->widget('Dialog');?>
        <?php $this->widget('Drawer');?>
        <?php $this->widget('Preview');?>
        <?php $this->widget('Confirm');?>
        <?php $this->content();?>
    </body>
</html>
