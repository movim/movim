<header>
    <ul class="tabs wide">
        <li {if="$type == 'all'"}class="active"{/if}>
            <a href="#" onclick="Menu_ajaxHttpGetAll(); Menu.setLoad(this);">{$c->__('menu.all')}</a>
        </li>
        <li {if="$type == 'feed'"}class="active"{/if}>
            <a href="#contacts" class="on_desktop" onclick="Menu_ajaxHttpGetContacts(); Menu.setLoad(this);" title="{$c->__('page.feed')}">
                {$c->__('page.contacts')}
            </a>
            <a href="#contacts" class="on_mobile" onclick="Menu_ajaxHttpGetContacts(); Menu.setLoad(this);" title="{$c->__('page.feed')}">
                <i class="material-symbols">people</i>
            </a>
        </li>
        <li {if="$type == 'news'"}class="active"{/if} >
            <a href="#communities" class="on_desktop" onclick="Menu_ajaxHttpGetCommunities(); Menu.setLoad(this);" title="{$c->__('page.news')}">
                {$c->__('page.communities')}
            </a>
            <a href="#communities" class="on_mobile" onclick="Menu_ajaxHttpGetCommunities(); Menu.setLoad(this);" title="{$c->__('page.news')}">
                <i class="material-symbols">group_work</i>
            </a>
        </li>
    </ul>
</header>

{if="$items && $items->isNotEmpty()"}
    <div id="menu_refresh"></div>
    <div class="card shadow" id="menu_wrapper">

    {loop="$items"}
        <div id="{$value->nodeid|cleanupId}" class="block large">
            {autoescape="off"}{$c->preparePost($value)}{/autoescape}
        </div>
    {/loop}

    <ul class="list thick">
        <li class="block">
            <div>
                <p class="center">
                    <a class="button flat {if="$page == 0"}disabled{/if}" href="#" onclick="MovimUtils.reload('{$next}')">
                        <i class="material-symbols">keyboard_arrow_left</i>
                        {$c->__('button.previous')}
                    </a>
                    {if="count($items) == $paging"}
                        <a class="button flat" href="#" onclick="MovimUtils.reload('{$next}')" title="{$c->__('post.older')}">
                            {$c->__('button.next')}
                            <i class="material-symbols">keyboard_arrow_right</i>
                        </a>
                    {/if}
                </p>
            </div>
        </li>
    </ul>

    </div>
{elseif="$page == 0"}
    <div id="menu_refresh"></div>
    <br/>

    <ul class="thick active divided spaced" id="menu_wrapper">
        <div class="placeholder">
            <i class="material-symbols">article</i>
            <h1>{$c->__('menu.empty_title')}</h1>
            <h4>{$c->__('menu.empty')}</h4>
            <h4>
                <br />
                <a class="button color green" href="{$c->route('explore')}">
                    <i class="material-symbols">explore</i>
                    {$c->__('button.discover')}
                </a>
            </h4>
        </div>
    </ul>
{/if}
