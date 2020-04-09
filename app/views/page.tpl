<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
        <title><%title%></title>

        <meta name="theme-color" content="#1C1D5B" />
        <%meta%>
        <meta name="application-name" content="<?php echo APP_TITLE; ?>">
        <link rel="manifest" href="<?php echo \Movim\Route::urlize('manifest'); ?>" />
        <link rel="shortcut icon" href="<?php $this->linkFile('img/favicon.ico');?>" />
        <link rel="icon" type="image/png" href="<?php $this->linkFile('img/app/48.png');?>" sizes="48x48">
        <link rel="icon" type="image/png" href="<?php $this->linkFile('img/app/96.png');?>" sizes="96x96">
        <link rel="icon" type="image/png" href="<?php $this->linkFile('img/app/128.png');?>" sizes="128x128">
        <script src="<?php echo BASE_URI; ?>scripts/favico.js"></script>
        <script src="<?php echo
            \Movim\Route::urlize('system') .
            '&t=' .
            filemtime(CACHE_PATH . 'socketapi.sock');
            ?>"></script>
        <meta name="viewport" content="width=device-width, user-scalable=no">
        <%scripts%>
    </head>
    <body dir="<%dir%>"
          class="<?php if (!$this->public && \App\User::me()->nightmode) { ?>nightmode<?php } ?>">
        <?php if ($this->js_check) { ?>
        <noscript>
            <style type="text/css">main {display: none;}</style>
            <ul class="list" style="color: white;">
                <li>
                    <content>
                        <p class="center"><?php echo __('global.no_js'); ?></p>
                    </content>
                </li>
            </ul>
        </noscript>
        <?php } ?>
        <div id="hiddendiv"></div>
        <div id="snackbar" class="snackbar"></div>
        <div id="status_websocket" class="snackbar hide">
            <ul class="list">
                <li>
                    <span class="control icon gray">
                        <i class="material-icons">signal_cellular_null</i>
                    </span>
                    <span class="control icon gray">
                        <i class="material-icons">signal_cellular_off</i>
                    </span>
                    <content>
                        <p class="normal">
                            <?php echo __('error.websocket_connect'); ?>
                        </p>
                        <p class="normal">
                            <?php echo __('error.websocket'); ?>
                        </p>
                    </content>
                </li>
            </ul>
        </div>
        <?php $this->widget('Dialog');?>
        <?php $this->widget('Drawer');?>
        <?php $this->widget('Preview');?>
        <?php $this->widget('Confirm');?>
        <%content%>
    </body>
</html>
