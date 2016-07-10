<div id="group_widget" class="spinner" style="background-color: #EEE;">
    <header class="fixed"></header>
    <div class="card shadow">
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
</div>
