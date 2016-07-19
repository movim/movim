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
                <span class="control icon active gray on_mobile" onclick="MovimTpl.showPanel()">
                    <i class="zmdi zmdi-eye"></i>
                </span>
                <p class="center line">{$c->__('page.news')}</p>
            </li>
        </ul>
        <ul class="tabs wide">
            <li {if="$type == 'all'"}class="active"{/if}><a href="#" onclick="Menu_ajaxGetAll()">{$c->__('menu.all')}</a></li>
            <li {if="$type == 'news'"}class="active"{/if} ><a href="#" onclick="Menu_ajaxGetNews()" title="{$c->__('page.news')}"><i class="zmdi zmdi-pages"></i></a></li>
            <li {if="$type == 'feed'"}class="active"{/if}><a href="#" onclick="Menu_ajaxGetFeed()" title="{$c->__('page.feed')}"><i class="zmdi zmdi-accounts"></i></a></li>
            <li {if="$type == 'me'"}class="active"{/if}><a href="#" onclick="Menu_ajaxGetMe()" title="{$c->__('menu.mine')}"><i class="zmdi zmdi-edit"></i></a></li>
        </ul>
    </header>
{/if}

{if="$items"}
    {if="$page == 0"}
        <div id="menu_refresh"></div>
        <ul class="list card shadow active flex stacked" id="menu_wrapper">
    {/if}

    {loop="$items"}
        {$attachments = $value->getAttachments()}
        <li
            tabindex="{$page*$paging+$key+1}"
            class="block large"
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
                <span class="primary icon thumb color white" style="background-image: linear-gradient(to bottom, rgba(0, 0, 0, 0.7) 0%, rgba(0, 0, 0, 0.3) 100%), url({$picture});">
                    {if="$value->isPublic()"}
                        <i title="{$c->__('menu.public')}" class="zmdi zmdi-portable-wifi"></i>
                    {/if}
                </span>
            {elseif="$value->node == 'urn:xmpp:microblog:0'"}
                {$url = $value->getContact()->getPhoto('l')}
                {if="$url"}
                    <span class="primary icon thumb color white" style="background-image: linear-gradient(to bottom, rgba(0, 0, 0, 0.7) 0%, rgba(0, 0, 0, 0.3) 100%), url({$url});">
                        {if="$value->isPublic()"}
                            <i title="{$c->__('menu.public')}" class="zmdi zmdi-portable-wifi"></i>
                        {/if}
                    </span>
                {else}
                    <span class="primary icon thumb color {$value->getContact()->jid|stringToColor}">
                        {if="$value->isPublic()"}
                            <i title="{$c->__('menu.public')}" class="zmdi zmdi-portable-wifi"></i>
                        {else}
                            <i class="zmdi zmdi-account"></i>
                        {/if}
                    </span>
                {/if}
            {else}
                <span class="primary icon thumb color {$value->node|stringToColor}">
                    {if="$value->isPublic()"}
                        <i title="{$c->__('menu.public')}" class="zmdi zmdi-portable-wifi"></i>
                    {else}
                        {$value->node|firstLetterCapitalize}
                    {/if}
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
                    </a>
                {else}
                    <a href="{$c->route('group', array($value->origin, $value->node))}">
                        <i class="zmdi zmdi-pages"></i> {$value->node}
                    </a>
                {/if}
                <span class="info">
                    {$value->published|strtotime|prepareDate:true,true}
                </span>
            </p>
            <p>{$value->contentcleaned|stripTags}</p>
        </li>
    {/loop}
    {if="count($items) == $paging"}
    <li id="history" class="block large" onclick="{$history} this.parentNode.removeChild(this);">
        <span class="icon primary gray">
            <i class="zmdi zmdi-time-restore"></i>
        </span>
        <p class="normal center line">{$c->__('post.older')}</p>
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
