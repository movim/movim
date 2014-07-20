<div class="breadcrumb">
    <a href="{$c->route('explore')}">
        <i class="fa fa-globe"></i> {$c->__('page.explore')}
    </a>
    <a href="{$c->route('server', $server)}">
        <i class="fa fa-sitemap"></i> {$server}
    </a>
    <a href="{$c->route('node', array($server, $node))}">
        {$name}
    </a>
    <a>{$c->__('page.configuration')}</a>
</div>
<div class="tabelem" title="{$c->t('Configuration')}" id="groupconfig">
    <h1 class="paddedtopbottom"><i class="fa fa-sliders"></i> {$c->__('page.configuration')}</h1>
    
    <div id="groupconfiguration" class="paddedtop">
        <div id="handlingmessages"></div>
        <a 
            class="button color green" 
            onclick="{$group_config} this.style.display = 'none'">
            <i class="fa fa-sliders"></i> {$c->__('group.config')}
        </a>
        <a 
            class="button color red" 
            onclick="{$group_delete} this.style.display = 'none'">
            <i class="fa fa-times"></i> {$c->__('group.delete')}
        </a>
    </div>
</div>
