<div id="subscribe">
    <h1>{$c->__('create.title')} {$c->__('on')} {$host}</h1>

    <div id="subscription_form">
        <h4>{$c->__('loading')}</h4>
    </div>

    <script type="text/javascript">
        MovimWebsocket.attach(function()
        {
            {$getsubscriptionform}
            AccountNext.host = '{$host}';
        });
    </script>
</div>

