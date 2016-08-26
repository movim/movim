<div class="tabelem padded" title="{$c->__('page.avatar')}" id="avatar" >
    <script type="text/javascript">
        MovimWebsocket.attach(function() {
            {$getavatar}
        });
    </script>
    <div id="avatar_form">
        <ul class="list thick">
            <li>
                <p class="center normal">{$c->__('global.loading')}</p>
            </li>
        </ul>
    </div>
</div>
