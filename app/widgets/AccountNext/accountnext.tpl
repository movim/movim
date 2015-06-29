<div id="subscribe">
    <div id="subscription_form" class="padded_right">
        <ul class="simple thick">
            <li>
                <span>{$c->__('create.title')} {$c->__('create.server_on')} {$host}</span>
                <p>{$c->__('create.loading')}</p>
            </li>
        </ul>
    </div>
    <script type="text/javascript">
        MovimWebsocket.attach(function()
        {
            MovimWebsocket.connection.register('{$host}');
            AccountNext.host = '{$host}';
        });

        MovimWebsocket.register(function()
        {
            {$getsubscriptionform}
        });
    </script>
</div>
