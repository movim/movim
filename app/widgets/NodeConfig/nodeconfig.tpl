<div class="breadcrumb">
    <a href="{$c->route('explore')}">
        {$c->t('Explore')}
    </a>
    <a href="{$c->route('server', $server)}">
        {$server}
    </a>
    <a href="{$c->route('node', array($server, $node))}">
        {$name}
    </a>
    <a>{$c->t('Configuration')}</a>
</div>
<div class="tabelem" title="{$c->t('Configuration')}" id="groupconfig">
    <h1>{$c->t('Configuration')}</h1>
    
    <div id="groupconfiguration" class="paddedtop">
        <div id="handlingmessages"></div>
        <a 
            class="button color green icon write" 
            onclick="{$group_config} this.style.display = 'none'">
            {$c->t('Configure your group')}
        </a>
        <a 
            class="button color red icon no" 
            onclick="{$group_delete} this.style.display = 'none'">
            {$c->t('Delete this group')}
        </a>
    </div>
</div>
