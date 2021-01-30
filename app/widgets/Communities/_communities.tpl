<ul class="tabs wide">
    <li {if="$type == 'all'"}class="active"{/if}>
        <a href="#" onclick="Communities_ajaxHttpGetAll();">{$c->__('menu.all')}</a>
    </li>
    <li {if="$type == 'news'"}class="active"{/if} >
        <a href="#communities" class="on_desktop" onclick="Communities_ajaxHttpGetNews();" title="{$c->__('page.news')}">
            {$c->__('page.communities')}
        </a>
        <a href="#communities" class="on_mobile" onclick="Communities_ajaxHttpGetNews();" title="{$c->__('page.news')}">
            <i class="material-icons">group_work</i>
        </a>
    </li>
    <li {if="$type == 'feed'"}class="active"{/if}>
        <a href="#contacts" class="on_desktop" onclick="Communities_ajaxHttpGetFeed();" title="{$c->__('page.feed')}">
            {$c->__('page.contacts')}
        </a>
        <a href="#contacts" class="on_mobile" onclick="Communities_ajaxHttpGetFeed();" title="{$c->__('page.feed')}">
            <i class="material-icons">people</i>
        </a>
    </li>
</ul>

<br />

<div id="communities_posts" class="spin" style="min-height: 30rem;"></div>
