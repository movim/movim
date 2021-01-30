<ul class="tabs wide">
    <li {if="$type == 'all'"}class="active"{/if}>
        <a href="#" onclick="Menu_ajaxHttpGetAll(); Menu.setLoad(this);">{$c->__('menu.all')}</a>
    </li>
    <li {if="$type == 'news'"}class="active"{/if} >
        <a href="#communities" class="on_desktop" onclick="Menu_ajaxHttpGetNews(); Menu.setLoad(this);" title="{$c->__('page.news')}">
            {$c->__('page.communities')}
        </a>
        <a href="#communities" class="on_mobile" onclick="Menu_ajaxHttpGetNews(); Menu.setLoad(this);" title="{$c->__('page.news')}">
            <i class="material-icons">group_work</i>
        </a>
    </li>
    <li {if="$type == 'feed'"}class="active"{/if}>
        <a href="#contacts" class="on_desktop" onclick="Menu_ajaxHttpGetFeed(); Menu.setLoad(this);" title="{$c->__('page.feed')}">
            {$c->__('page.contacts')}
        </a>
        <a href="#contacts" class="on_mobile" onclick="Menu_ajaxHttpGetFeed(); Menu.setLoad(this);" title="{$c->__('page.feed')}">
            <i class="material-icons">people</i>
        </a>
    </li>
</ul>

<div id="communities_posts">
    {autoescape="off"}
        {$c->preparePosts()}
    {/autoescape}
</div>