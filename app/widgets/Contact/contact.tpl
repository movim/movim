{if="$jid"}
    <!--<script type="text/javascript">
        MovimWebsocket.attach(function() {
            Contact_ajaxGetContact('{$jid}');
        });
    </script>-->
    {$c->prepareContact($jid)}
{else}
    <div title="{$c->__('page.profile')}">
        {$c->prepareEmpty()}
    </div>
{/if}
