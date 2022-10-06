<!DOCTYPE html>
<html translate="no">
    <head>
        <meta charset="utf-8" />
        <title><%title%></title>

        <meta name="theme-color" content="<?php if (!$this->public && isLogged() && \App\User::me()->nightmode) { ?>#10151A<?php } else { ?>#1C1D5B<?php } ?>" />
        <%meta%>
        <meta name="application-name" content="<?php echo APP_TITLE; ?>">
        <link rel="manifest" href="<?php echo \Movim\Route::urlize('manifest'); ?>" />
        <link rel="apple-touch-icon" href="<?php $this->linkFile('img/app/192_square.png');?>"/>
        <link rel="icon" type="image/png" href="<?php $this->linkFile('img/app/128.png');?>" sizes="128x128">
        <script src="<?php echo
            \Movim\Route::urlize('system') .
            '&t=' .
            filemtime(CACHE_PATH . 'socketapi.sock');
            ?>"></script>
        <meta name="viewport" content="width=device-width, user-scalable=no">
        <%scripts%>
    </head>
    <body dir="<%dir%>"
          class="<?php if (!$this->public && isLogged() && \App\User::me()->nightmode) { ?>nightmode<?php } ?>">
        <?php if ($this->js_check) { ?>
        <noscript>
            <style type="text/css">main {display: none;}</style>
            <ul class="list" style="color: white;">
                <li>
                    <div>
                        <p class="center"><?php echo __('global.no_js'); ?></p>
                    </div>
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
                    <div>
                        <p class="normal">
                            <?php echo __('error.websocket_connect'); ?>
                        </p>
                        <p class="normal">
                            <?php echo __('error.websocket'); ?>
                        </p>
                    </div>
                </li>
            </ul>
        </div>
        <?php $this->widget('Dialog');?>
        <?php $this->widget('Drawer');?>
        <?php $this->widget('Confirm');?>
        <?php $this->widget('Preview');?>
        <%content%>
    </body>
</html>
