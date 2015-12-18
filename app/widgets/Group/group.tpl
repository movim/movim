<div id="group_widget" class="card shadow spinner" style="background-color: #EEE;">
    {$c->prepareEmpty()}
    {if="$server && $node"}
        <script type="text/javascript">
            MovimWebsocket.attach(function() {
                Group_ajaxGetItems('{$server}', '{$node}');
                Group_ajaxGetMetadata('{$server}', '{$node}');
                Group_ajaxGetAffiliations('{$server}', '{$node}');
            });
        </script>
    {/if}
</div>
