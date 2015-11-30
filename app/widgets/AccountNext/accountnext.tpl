<div id="subscribe">
    <div class="flex">
        <div class="block on_desktop">
            <div class="placeholder icon account">
            <h4>{$c->__('create.title')}</h4>
            <h4>{$c->__('create.placeholder')}</h4>
            </div>
        </div>

        <div id="subscription_form" class="block">
            <ul class="simple thick">
                <li>
                    <span>{$c->__('create.title')} {$c->__('create.server_on')} {$host}</span>
                    <p>{$c->__('create.loading')}</p>
                </li>
            </ul>
        </div>
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
