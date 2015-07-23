<div title="{$c->__('page.profile')}">
    {$c->prepareEmpty()}
    {if="$jid"}
        <script type="text/javascript">
            MovimWebsocket.attach(function() {
                Contact_ajaxGetContact('{$jid}');
            });
        </script>
    {/if}
</div>
