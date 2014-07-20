<div class="breadcrumb protect red ">
    <a href="{$c->route('explore')}">
        <i class="fa fa-globe"></i> {$c->__('page.explore')}
    </a>
    <a href="{$c->route('server', $_GET['s'])}">
        <i class="fa fa-sitemap"></i> {$_GET['s']}
    </a>
    <a>{$c->__('topics')}</a>
</div> 
<div class="posthead paddedtopbottom" id="servernodeshead">
    <a
        href="#"
        onclick="{$get_nodes}; 
            this.className='button icon loading color orange'; this.onclick=null;"
        class="button color">
        <i class="fa fa-refresh"></i> {$c->__('button.refresh')}
    </a>
</div>
<div id="servernodes" class="tabelem paddedtop" title="{$c->__('page.server')}">
    <div id="newGroupForm"></div>
    <div id="servernodeslist">
        {$server}
    </div>
</div>
