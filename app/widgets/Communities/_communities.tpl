<ul class="tabs wide">
    <li {if="$type == 'all'"}class="active"{/if}>
        <a href="#" onclick="Communities_ajaxHttpGetAll();">{$c->__('menu.all')}</a>
    </li>
    <li {if="$type == 'feed'"}class="active"{/if}>
        <a href="#" class="on_desktop" onclick="Communities_ajaxHttpGetContacts();" title="{$c->__('page.feed')}">
            {$c->__('page.contacts')}
        </a>
        <a href="#" class="on_mobile" onclick="Communities_ajaxHttpGetContacts();" title="{$c->__('page.feed')}">
            <i class="material-symbols">people</i>
        </a>
    </li>
    <li {if="$type == 'news'"}class="active"{/if} >
        <a href="#" class="on_desktop" onclick="Communities_ajaxHttpGetCommunities();" title="{$c->__('page.news')}">
            {$c->__('page.communities')}
        </a>
        <a href="#" class="on_mobile" onclick="Communities_ajaxHttpGetCommunities();" title="{$c->__('page.news')}">
            <i class="material-symbols">group_work</i>
        </a>
    </li>
</ul>

<div id="communities_posts" class="spin" style="min-height: 30rem;"></div>
