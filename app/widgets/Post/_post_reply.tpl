{if="$post->isReply()"}
    <hr />
    {$reply = $post->getReply()}
    {if="$reply"}
        <ul class="list thick active recessed"
            onclick="MovimUtils.reload('{$c->route('post', [$reply->server, $reply->node, $reply->nodeid])}')">
            <li>
                {if="$reply->picture"}
                    <span
                        class="primary icon bubble color white"
                        style="background-image: linear-gradient(to bottom, rgba(0, 0, 0, 0.7) 0%, rgba(0, 0, 0, 0.3) 100%), url({$reply->picture});">
                        <i class="zmdi zmdi-share"></i>
                    </span>
                {elseif="$reply->isMicroblog()"}
                    {$url = $reply->contact->getPhoto('l')}
                    {if="$url"}
                        <span class="primary icon bubble color white" style="background-image: linear-gradient(to bottom, rgba(0, 0, 0, 0.7) 0%, rgba(0, 0, 0, 0.3) 100%), url({$url});">
                            <i class="zmdi zmdi-share"></i>
                        </span>
                    {else}
                        <span class="primary icon bubble color {$reply->contact->jid|stringToColor}">
                            <i class="zmdi zmdi-share"></i>
                        </span>
                    {/if}
                {/if}
                <span class="control icon gray">
                    <i class="zmdi zmdi-chevron-right"></i>
                </span>
                <p class="normal line">{$reply->title}</p>
                <p>{$reply->getContent()|html_entity_decode|stripTags}</p>
                <p>
                    {if="$reply->isMicroblog()"}
                        <i class="zmdi zmdi-account"></i> {$reply->contact->truename}
                    {else}
                        <i class="zmdi zmdi-pages"></i> {$reply->node}
                    {/if}
                    <span class="info">
                        {$reply->published|strtotime|prepareDate:true,true}
                    </span>
                </p>
            </li>
        </ul>
    {else}
        <ul class="list thick active faded">
            <li>
                <span class="primary icon gray">
                    <i class="zmdi zmdi-mail-reply"></i>
                </span>
                <p class="line normal">{$c->__('post.serveral_deleted')}</p>
            </li>
        </ul>
    {/if}
{/if}

