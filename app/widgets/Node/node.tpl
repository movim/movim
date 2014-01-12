<div class="tabelem protect red" id="node" title="{$c->t('Posts')}">
    <div class="breadcrumb">
        <a href="{$c->route('explore')}">
            {$c->t('Explore')}
        </a>
        <a href="{$c->route('server', $server)}">
            {$server}
        </a>
        <a href="{$c->route('node', array($server, $node))}">
            {$title}
        </a>
        <a>{$c->t('Posts')}</a>
    </div>
    <div class="clear"></div>
    
    <div class="metadata" id="metadata">

    </div>
    
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
