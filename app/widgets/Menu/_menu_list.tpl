<header>
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
</header>

{if="$type == 'me' && $c->getUser()->hasPubsub() && $page == 0"}
    <ul class="list active on_desktop flex">
        <a href="{$c->route('contact', $jid)}" class="block">
            <li>
                <span class="primary icon">
                    <i class="material-icons">person</i>
                </span>
                <span class="control icon">
                    <i class="material-icons">chevron_right</i>
                </span>
                <div>
                    <p class="normal line">{$c->__('privacy.my_profile')}</p>
                </div>
            </li>
        </a>
        <a href="{$c->route('blog', $jid)}" target="_blank" class="block">
            <li>
                <span class="primary icon">
                    <i class="material-icons">wifi_tethering</i>
                </span>
                <span class="control icon">
                    <i class="material-icons">chevron_right</i>
                </span>
                <div>
                    <p class="normal line">{$c->__('hello.blog_title')}</p>
                </div>
            </li>
        </a>
    </ul>
{/if}

{if="$items->isNotEmpty()"}
    <div id="menu_refresh"></div>
    <div class="list card shadow" id="menu_wrapper">

    {loop="$items"}
        <div id="{$value->nodeid|cleanupId}" class="block large">
            {autoescape="off"}{$c->preparePost($value)}{/autoescape}
        </div>
    {/loop}

    <ul class="list thick">
        <li class="block">
            <div>
                <p class="center">
                    <a class="button flat {if="$page == 0"}disabled{/if}" href="{$previous}">
                        <i class="material-icons">keyboard_arrow_left</i>
                        {$c->__('button.previous')}
                    </a>
                    {if="count($items) == $paging"}
                        <a class="button flat" href="{$next}" title="{$c->__('post.older')}">
                            {$c->__('button.next')}
                            <i class="material-icons">keyboard_arrow_right</i>
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
            <i class="material-icons">receipt</i>
            <h1>{$c->__('menu.empty_title')}</h1>
            <h4>{$c->__('menu.empty')}</h4>
        </div>
    </ul>
{/if}
