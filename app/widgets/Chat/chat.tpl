<div id="chat_widget">
    {$c->prepareEmpty()}
    {if="$jid"}
        <script type="text/javascript">
            MovimWebsocket.attach(function() {
                Chat_ajaxGet('{$jid}');
            });
        </script>
    {/if}
    {if="$room"}
        <script type="text/javascript">
            MovimWebsocket.attach(function() {
                Chat_ajaxGetRoom('{$room}');
            });
        </script>
    {/if}
</div>
