<div class="tabelem protect red" id="node" title="{$c->__('page.posts')}">
    <div class="breadcrumb">
        <a href="{$c->route('explore')}">
            <i class="fa fa-globe"></i> {$c->__('page.explore')}
        </a>
        <a href="{$c->route('server', $server)}">
            <i class="fa fa-sitemap"></i> {$server}
        </a>
        <a href="{$c->route('node', array($server, $node))}">
            {$title}
        </a>
        <a>{$c->__('page.posts')}</a>
    </div>
    <div class="clear"></div>
    
    <div class="metadata paddedtopbottom" id="metadata">
        {$metadata}
    </div>
    <br />
    <div id="formpublish" style="padding-bottom: 1em; display: none;">
        {$formpublish}
    </div>
    <div id="{$hash}">
        {$items}
    </div>
</div>
<script type="text/javascript">
    {$getaffiliations}
    setTimeout("{$getmetadata}", 1000);
</script>
