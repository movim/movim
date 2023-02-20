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
            filemtime(CACHE_PATH_RESOLVED . 'socketapi.sock');
            ?>"></script>
        <meta name="viewport" content="width=device-width, user-scalable=no">
        <%scripts%>
    </head>
    <body dir="<%dir%>"
          class="<?php if (!$this->public && isLogged() && \App\User::me()->nightmode) { ?>nightmode<?php } ?>">
        <%common%>
        <%content%>
    </body>
</html>
