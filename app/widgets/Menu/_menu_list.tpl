{if="$items"}
    {if="$page == 0"}
        <div id="menu_refresh"></div>
        <ul class="card shadow active flex stacked" id="menu_wrapper">
    {/if}

    {loop="$items"}
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
                <span class="icon thumb color red tiny">
                    +18
                </span>
            {elseif="$picture != null"}
                <span class="icon thumb" style="background-image: url({$picture});"></span>
            {elseif="$value->node == 'urn:xmpp:microblog:0'"}
                {$url = $value->getContact()->getPhoto('l')}
                {if="$url"}
                    <span class="icon thumb" style="background-image: url({$url});">
                    </span>
                {else}
                    <span class="icon thumb color {$value->getContact()->jid|stringToColor}">
                        <i class="zmdi zmdi-account"></i>
                    </span>
                {/if}
            {else}
                <span class="icon thumb color {$value->node|stringToColor}">{$value->node|firstLetterCapitalize}</span>
            {/if}

            {if="$value->title != null"}
                <span>{$value->title}</span>
            {else}
                <span>{$c->__('menu.contact_post')}</span>
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
            </p>

            {if="$value->privacy"}
                <span class="info" title="{$c->__('menu.public')}">
                    <i class="zmdi zmdi-portable-wifi"></i>
                </span>
            {/if}
            <p>{$value->contentcleaned|strip_tags}</p>
        </li>
    {/loop}
    {if="count($items) == $paging"}
    <li id="history" class="block large" onclick="{$history} this.parentNode.removeChild(this);">
        <span class="icon"><i class="zmdi zmdi-time-restore"></i></span>
        <span>{$c->__('post.older')}</span>
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
