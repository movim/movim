<div id="subscribe">
    <div id="subscription_form">
        <ul class="simple thick">
            <li>
                <span>{$c->__('create.title')} {$c->__('on')} {$host}</span>
                <p>{$c->__('loading')}</p>
            </li>
        </ul>
    </div>

    <script type="text/javascript">
        MovimWebsocket.attach(function()
        {
            {$getsubscriptionform}
            AccountNext.host = '{$host}';
        });
    </script>
</div>

