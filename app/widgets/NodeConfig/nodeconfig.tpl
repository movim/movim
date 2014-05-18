<div class="breadcrumb">
    <a href="{$c->route('explore')}">
        {$c->__('page.explore')}
    </a>
    <a href="{$c->route('server', $server)}">
        {$server}
    </a>
    <a href="{$c->route('node', array($server, $node))}">
        {$name}
    </a>
    <a>{$c->__('page.configuration')}</a>
</div>
<div class="tabelem" title="{$c->t('Configuration')}" id="groupconfig">
    <h1>{$c->__('page.configuration')}</h1>
    
    <div id="groupconfiguration" class="paddedtop">
        <div id="handlingmessages"></div>
        <a 
            class="button color green icon write" 
            onclick="{$group_config} this.style.display = 'none'">
            {$c->__('group.config')}
        </a>
        <a 
            class="button color red icon no" 
            onclick="{$group_delete} this.style.display = 'none'">
            {$c->__('group.delete')}
        </a>
    </div>
</div>
