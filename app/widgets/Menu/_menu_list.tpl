{if="$page == 0"}
    <header>
        <ul class="list middle">
            <li>
                <span id="menu" class="primary on_mobile icon active gray" onclick="MovimTpl.toggleMenu()"><i class="zmdi zmdi-menu"></i></span>
                <span class="primary on_desktop icon gray"><i class="zmdi zmdi-filter-list"></i></span>
                <p class="center line">{$c->__('page.news')}</p>
            </li>
        </ul>
        <ul>
            <li>
                <ul class="tabs wide">
                    <li {if="$type == 'all'"}class="active"{/if}><a href="#" onclick="Menu_ajaxGetAll()">{$c->__('menu.all')}</a></li>
                    <li {if="$type == 'news'"}class="active"{/if} ><a href="#" onclick="Menu_ajaxGetNews()" title="{$c->__('page.news')}"><i class="zmdi zmdi-pages"></i></a></li>
                    <li {if="$type == 'feed'"}class="active"{/if}><a href="#" onclick="Menu_ajaxGetFeed()" title="{$c->__('page.feed')}"><i class="zmdi zmdi-accounts"></i></a></li>
                    <li {if="$type == 'me'"}class="active"{/if}><a href="#" onclick="Menu_ajaxGetMe()" title="{$c->__('menu.mine')}"><i class="zmdi zmdi-portable-wifi"></i></a></li>
                </ul>
            </li>
        </ul>
    </header>
{/if}

{if="$items"}
    {if="$page == 0"}
        <div id="menu_refresh"></div>
        <ul class="list card shadow active flex stacked" id="menu_wrapper">
    {/if}

    {loop="$items"}
        {$attachements = $value->getAttachements()}
        <li
            tabindex="{$page*$paging+$key+1}"
            class="block large condensed "
            data-id="{$value->nodeid}"
            data-server="{$value->origin}"
            data-node="{$value->node}"
            {if="$value->title != null"}
                title="{$value->title|strip_tags}"
            {else}
                title="{$c->__('menu.contact_post')}"
            {/if}
        >
            {$picture = $value->getPicture()}
            {if="current(explode('.', $value->origin)) == 'nsfw'"}
                <span class="primary icon thumb color red tiny">
                    +18
                </span>
            {elseif="$picture != null"}
                <span class="primary icon thumb" style="background-image: url({$picture});"></span>
            {elseif="$value->node == 'urn:xmpp:microblog:0'"}
                {$url = $value->getContact()->getPhoto('l')}
                {if="$url"}
                    <span class="primary icon thumb" style="background-image: url({$url});">
                    </span>
                {else}
                    <span class="primary icon thumb color {$value->getContact()->jid|stringToColor}">
                        <i class="zmdi zmdi-account"></i>
                    </span>
                {/if}
            {else}
                <span class="primary icon thumb color {$value->node|stringToColor}">{$value->node|firstLetterCapitalize}</span>
            {/if}

            {if="$value->isPublic()"}
                <span class="control icon gray" title="{$c->__('menu.public')}">
                    <i class="zmdi zmdi-portable-wifi"></i>
                </span>
            {/if}

            {if="$value->title != null"}
                <p class="line">{$value->title}</p>
            {else}
                <p class="line">{$c->__('menu.contact_post')}</p>
            {/if}
            <p>
                {if="$value->node == 'urn:xmpp:microblog:0'"}
                    <a href="{$c->route('contact', $value->getContact()->jid)}">
                        <i class="zmdi zmdi-account"></i> {$value->getContact()->getTrueName()}
                    </a> –
                {else}
                    {$value->origin} /
                    <a href="{$c->route('group', array($value->origin, $value->node))}">
                        <i class="zmdi zmdi-pages"></i> {$value->node}
                    </a> –
                {/if}
                {$value->published|strtotime|prepareDate}
                {if="$value->published != $value->updated"}<i class="zmdi zmdi-edit"></i>{/if}
            </p>
            <p>{$value->contentcleaned|stripTags}</p>
        </li>
    {/loop}
    {if="count($items) == $paging"}
    <li id="history" class="block large" onclick="{$history} this.parentNode.removeChild(this);">
        <span class="icon primary"><i class="zmdi zmdi-time-restore"></i></span>
        <p class="normal center">{$c->__('post.older')}</p>
    </li>
    {/if}

    {if="$page == 0"}
        </ul>
    {/if}
{elseif="$page == 0"}
    <div id="menu_refresh"></div>
    <br/>

    <ul class="thick active divided spaced" id="menu_wrapper">
        <div class="placeholder icon news">
            <h1>{$c->__('menu.empty_title')}</h1>
            <h4>{$c->__('menu.empty')}</h4>
        </div>
    </ul>

{/if}
