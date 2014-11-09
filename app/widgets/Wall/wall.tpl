<div class="tabelem" id="wall" title="{$c->__('feed.title')}" >
    <div class="protect orange" title="{function="getFlagTitle("orange")"}"></div>
    <div id="{function="stringToUri($_GET['f'].'urn:xmpp:microblog:0')"}">
        {$wall = $c->prepareFeed(-1, $_GET['f'])}
        {if="$wall"}
            {$wall}
        {else}
            <div style="padding: 1.5em; text-align: center;">Ain't Nobody Here But Us Chickens...</div>
            <script type="text/javascript">
                MovimWebsocket.attach(function() {
                    {$refresh}
                });
            </script>
        {/if}
        <div class="spacetop clear"></div>
    </div>
</div>
