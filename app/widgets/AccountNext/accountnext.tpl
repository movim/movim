<div id="subscribe">
    <div class="flex">
        <div class="block on_desktop">
            <div class="placeholder">
                <i class="material-icons">person_add</i>
                <h4>{$c->__('create.title')}</h4>
                <h4>{$c->__('create.placeholder')}</h4>
            </div>
        </div>

        <div id="subscription_form" class="block">
            <ul class="list simple thick">
                <li>
                    <div>
                        <p>{$c->__('create.title')} {$c->__('create.server_on')} {$host}</p>
                        <p>{$c->__('create.loading')}</p>
                    </div>
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
            AccountNext_ajaxGetForm('{$host}');
        });
    </script>
</div>
