<div id="group_widget" class="divided spinner">
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
