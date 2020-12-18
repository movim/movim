<br />
<hr />
{if="$reply != null"}
    <ul class="list thick active recessed"
        onclick="window.location.replace('{$c->route('post', [$reply->server, $reply->node, $reply->nodeid])}')">
        <li>
            {if="$reply->picture"}
                <span
                    class="primary icon gray"
                    style="background-image: linear-gradient(to bottom, rgba(0, 0, 0, 0.7) 0%, rgba(0, 0, 0, 0.3) 100%), url({$reply->picture});">
                    <i class="material-icons flip-hor">reply</i>
                </span>
            {elseif="$reply->isMicroblog() && $reply->contact"}
                {$url = $reply->contact->getPhoto('l')}
                {if="$url"}
                    <span class="primary icon gray" style="background-image: linear-gradient(to bottom, rgba(0, 0, 0, 0.7) 0%, rgba(0, 0, 0, 0.3) 100%), url({$url});">
                        <i class="material-icons">reply</i>
                    </span>
                {else}
                    <span class="primary icon gray {$reply->contact->jid|stringToColor}">
                        <i class="material-icons">reply</i>
                    </span>
                {/if}
            {/if}
            <span class="control icon gray">
                <i class="material-icons">chevron_right</i>
            </span>
            <div>
                <p class="normal line">{$reply->title}</p>
                <p>{$reply->getContent()|html_entity_decode|stripTags}</p>
                <p>
                    {if="$reply->isMicroblog() && $reply->contact"}
                        <i class="material-icons">people</i> {$reply->contact->truename}
                    {else}
                        <i class="material-icons">group_work</i> {$reply->node}
                    {/if}
                    <span class="info">
                        {$reply->published|strtotime|prepareDate:true,true}
                    </span>
                </p>
            </div>
        </li>
    </ul>
{else}
    <ul class="list thick active faded">
        <li>
            <span class="primary icon gray">
                <i class="material-icons">reply</i>
            </span>
            <div>
                <p class="line normal">{$c->__('post.original_deleted')}</p>
            </div>
        </li>
    </ul>
{/if}
