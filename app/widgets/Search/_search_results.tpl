{if="$posts->isEmpty() && $contacts->isEmpty()"}
    {$c->prepareEmpty()}
{/if}

{if="$posts->isNotEmpty()"}
<ul class="list active divided middle">
    <li class="subheader"><p>{$c->__('page.news')}</p></li>
    {loop="$posts"}
        {$c->prepareTicket($value)}
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
            <span class="control icon active gray" onclick="Search_ajaxChat('{$value->jid}')">
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
