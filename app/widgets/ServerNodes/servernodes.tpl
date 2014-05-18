<div class="breadcrumb protect red ">
    <a href="{$c->route('explore')}">{$c->__('page.explore')}</a>
    <a href="{$c->route('server', $_GET['s'])}">
        {$_GET['s']}
    </a>
    <a>{$c->__('topics')}</a>
</div> 
<div class="posthead " id="servernodeshead">
    <a
        href="#"
        onclick="{$get_nodes}; 
            this.className='button icon loading color orange'; this.onclick=null;"
        class="button icon refresh color">
        {$c->__('button.refresh')}
    </a>
</div>
<div id="servernodes" class="tabelem paddedtop" title="{$c->__('page.server')}">
    <div id="newGroupForm"></div>
    <div id="servernodeslist">
        {$server}
    </div>
</div>
