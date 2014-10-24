<div class="tabelem" title="{$c->__('title')}" id="groupsubscribedlistfromfriend">
    <div class="protect red" title="{function="getFlagTitle("red")"}"></div>
    <h1 class="paddedtopbottom">{$c->__('title')}</h1>
    <script type="text/javascript">
        movim_add_onload(function() {
            {$refresh}
        });
    </script>
    <div id="publicgroups" class="paddedtop"></div>
</div>
