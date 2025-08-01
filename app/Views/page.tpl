<!DOCTYPE html>
<html translate="no" lang="<%language%>">
    <head>
        <meta charset="utf-8" />
        <title><%title%></title>

        <meta name="theme-color" media="(prefers-color-scheme: light)" content="<?php if (!$this->public && isLogged() && me()->nightmode) { ?>rgb(25, 32, 40)<?php } else { ?>rgb(238, 238, 238)<?php } ?>" />
        <meta name="theme-color" media="(prefers-color-scheme: dark)" content="rgb(25, 32, 40)" />

        <%meta%>
        <meta name="application-name" content="<?php echo APP_TITLE; ?>">
        <link rel="manifest" href="<?php echo \Movim\Route::urlize('manifest'); ?>" />
        <link rel="apple-touch-icon" href="<?php $this->linkFile('img/app/192_square.png');?>"/>
        <link rel="icon" type="image/png" href="<?php $this->linkFile('img/app/128.png');?>" sizes="128x128">
        <link rel="stylesheet" href="<?php echo
            \Movim\Route::urlize('colors') .
            '?t=' .
            filemtime(CACHE_PATH . 'socketapi.sock');
            ?>" type="text/css" />
        <script src="<?php echo
            \Movim\Route::urlize('system') .
            '?t=' .
            filemtime(CACHE_PATH . 'socketapi.sock');
            ?>" defer></script>
        <meta name="viewport" content="width=device-width, user-scalable=no">
        <%scripts%>
    </head>
    <body dir="<%dir%>"
          class="<?php if (!$this->public && isLogged() && me()->nightmode) { ?>nightmode<?php } ?>">
        <%common%>
        <%content%>
    </body>
</html>
