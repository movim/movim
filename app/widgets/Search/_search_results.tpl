{if="$posts->isEmpty() && $contacts->isEmpty() && $tags->isEmpty()"}
    {autoescape="off"}
        {$c->prepareEmpty()}
    {/autoescape}
{/if}

{if="$posts->isNotEmpty()"}
<ul id="search_posts" class="list card active middle">
    <li class="subheader">
        <p>
            <span class="info">{$posts|count}</span>
            {$c->__('page.news')}
        </p>
    </li>
    {loop="$posts"}
        {autoescape="off"}
            {$c->prepareTicket($value)}
        {/autoescape}
    {/loop}
</ul>
{/if}

{if="$tags->isNotEmpty()"}
<ul class="list active">
    <li class="subheader">
        <p>{$c->__('search.tags')}</p>
    </li>
    {loop="$tags"}
        <li onclick="MovimUtils.redirect('{$c->route('tag', $value)}')">
            <span class="primary icon gray">
                #
            </span>
            <span class="control icon gray">
                <i class="material-icons">chevron_right</i>
            </span>
            <p class="line normal">{$value}</p>
            <p>{$c->__('communitydata.num', $key)}</p>
        </li>
    {/loop}
</ul>
{/if}

{if="$contacts->isNotEmpty()"}
<ul class="list">
    <li class="subheader">
        <p>{$c->__('explore.explore')}</p>
    </li>
    {loop="$contacts"}
        <li
            id="{$value->jid|cleanupId}"
            title="{$value->jid}"
        >
            {$url = $value->getPhoto('m')}
            {if="$url"}
                <span class="primary icon bubble"
                    style="background-image: url({$url});">
                </span>
            {else}
                <span class="primary icon bubble color {$value->jid|stringToColor}">
                    <i class="material-icons">person</i>
                </span>
            {/if}
            <span class="control icon active gray" onclick="MovimUtils.reload('{$c->route('contact', $value->jid)}')">
                <i class="material-icons">person</i>
            </span>
            <span class="control icon active gray" onclick="Search.chat('{$value->jid|echapJS}')">
                <i class="material-icons">comment</i>
            </span>
            <p class="normal line">{$value->truename}</p>
            {if="$value->isEmpty()"}
                <p>{$value->jid}</p>
            {/if}
        </li>
    {/loop}
    </ul>
{/if}
