<div id="subscribe">
    <h1 class="paddedtopbottom">{$c->__('create.title')} {$c->__('on')} {$host}</h1>

    <div id="subscription_form" class="paddedtopbottom">
        {$c->__('loading')}
    </div>

    <script type="text/javascript">
        MovimWebsocket.attach(function()
        {
            {$getsubscriptionform}
            AccountNext.host = '{$host}';
        });
    </script>
</div>

