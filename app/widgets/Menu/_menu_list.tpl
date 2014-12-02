<div id="menu_refresh"></div>
{if="$items"}
    <ul>
        {loop="$items"}
            <li class="padded" data-id="{$value->nodeid}">
                <span class="tag">{$value->node}</span>
                {if="current(explode('.', $value->jid)) == 'nsfw'"}
                    <span class="tag">NSFW</span>
                {/if}
                <h1>{$value->title}</h1>
                <span class="date">{$value->published|strtotime|prepareDate}</span>
            </li>
        {/loop}

        <li class="older" onclick="{$history} this.parentNode.removeChild(this);">
            <i class="fa fa-history"></i> {$c->__('post.older')}
        </li>
    </ul>
{elseif="$page == 0"}
    <div class="placeholder padded">
        <h1>{$c->__('menu.empty_title')}</h1>
        <p>{$c->__('menu.empty')}</p>
    </div>
{/if}
