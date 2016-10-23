<script type="text/javascript">
    if(typeof navigator.registerProtocolHandler == 'function') {
        navigator.registerProtocolHandler('xmpp',
                                      '{$c->route("share")}/%s',
                                      'Movim');
    }

    var BASE_URI        = '{$base_uri}';
    var BASE_HOST       = '{$base_host}';
    var ERROR_URI       = '{$error_uri}';
    var PAGE_KEY_URI    = '{$page_key_uri}';
    var CURRENT_PAGE    = '{$current_page}';
    var SERVER_CONF     = {$server_conf};
    var SECURE_WEBSOCKET= {$secure_websocket};
    var SMALL_PICTURE_LIMIT = {$small_picture_limit};
</script>
