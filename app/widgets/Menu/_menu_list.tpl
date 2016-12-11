{if="$page == 0"}
    <header>
        <ul class="list middle">
            <li>
                <span id="menu" class="primary on_mobile icon active gray" onclick="MovimTpl.toggleMenu()">
                    <i class="zmdi zmdi-menu"></i>
                </span>
                <span class="primary on_desktop icon gray">
                    <i class="zmdi zmdi-filter-list"></i>
                </span>
                <!--<span class="control icon active gray on_mobile" onclick="MovimTpl.showPanel()">
                    <i class="zmdi zmdi-eye"></i>
                </span>-->
                <p class="center line">{$c->__('page.news')}</p>
            </li>
        </ul>
        <ul class="tabs wide">
            <li {if="$type == 'all'"}class="active"{/if}>
                <a href="#" onclick="Menu_ajaxGetAll()">{$c->__('menu.all')}</a>
            </li>
            <li {if="$type == 'news'"}class="active"{/if} >
                <a href="#" class="on_desktop" onclick="Menu_ajaxGetNews()" title="{$c->__('page.news')}">
                    {$c->__('page.communities')}
                </a>
                <a href="#" class="on_mobile" onclick="Menu_ajaxGetNews()" title="{$c->__('page.news')}">
                    <i class="zmdi zmdi-group-work"></i>
                </a>
            </li>
            <li {if="$type == 'feed'"}class="active"{/if}>
                <a href="#" class="on_desktop" onclick="Menu_ajaxGetFeed()" title="{$c->__('page.feed')}">
                    {$c->__('page.contacts')}
                </a>
                <a href="#" class="on_mobile" onclick="Menu_ajaxGetFeed()" title="{$c->__('page.feed')}">
                    <i class="zmdi zmdi-accounts"></i>
                </a>
            </li>
            <li {if="$type == 'me'"}class="active"{/if}>
                <a href="#" onclick="Menu_ajaxGetMe()" title="{$c->__('menu.mine')}">
                    {$c->__('page.blog')}
                </a>
            </li>
        </ul>
    </header>
{/if}

{if="$type == 'me' && $c->supported('pubsub')"}
    <ul class="list active on_desktop flex">
        <a href="{$c->route('contact', $jid)}" class="block">
            <li>
                <span class="primary icon">
                    <i class="zmdi zmdi-account"></i>
                </span>
                <span class="control icon">
                    <i class="zmdi zmdi-chevron-right"></i>
                </span>
                <p class="normal line">{$c->__('privacy.my_profile')}</p>
            </li>
        </a>
        <a href="{$c->route('blog', $jid)}" target="_blank" class="block">
            <li>
                <span class="primary icon">
                    <i class="zmdi zmdi-portable-wifi"></i>
                </span>
                <span class="control icon">
                    <i class="zmdi zmdi-chevron-right"></i>
                </span>
                <p class="normal line">{$c->__('hello.blog_title')}</p>
            </li>
        </a>
    </ul>
{/if}

{if="$items"}
    {if="$page == 0"}
        <div id="menu_refresh"></div>
        <div class="list card shadow" id="menu_wrapper">
    {/if}

    {loop="$items"}
        {$c->preparePost($value)}
    {/loop}
    {if="count($items) == $paging"}
        <ul class="list active thick">
            <li id="history" class="large" onclick="{$history} this.parentNode.removeChild(this);">
                <span class="icon primary gray">
                    <i class="zmdi zmdi-time-restore"></i>
                </span>
                <p class="normal center">{$c->__('post.older')}</p>
            </li>
        </ul>
    {/if}

    {if="$page == 0"}
        </div>
    {/if}
{elseif="$page == 0"}
    <div id="menu_refresh"></div>
    <br/>

    <ul class="thick active divided spaced" id="menu_wrapper">
        <div class="placeholder icon news">
            <h1>{$c->__('menu.empty_title')}</h1>
            <h4>{$c->__('menu.empty')}</h4>
            <a class="button flat on_mobile"
               onclick="MovimTpl.showPanel()">
               <i class="zmdi zmdi-eye"></i>  {$c->__('button.discover')}
            </a>
        </div>
    </ul>

{/if}
