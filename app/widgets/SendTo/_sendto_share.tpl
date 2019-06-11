<section id="sendto">
    {if="$card"}
        <ul class="list card middle">
            <li class="subheader">
                <p>{$c->__('button.share')}</p>
            </li>
            {autoescape="off"}
                {$card}
            {/autoescape}
            <li>
                <span class="control icon active gray"
                      onclick="MovimUtils.redirect('{$c->route('publish', [$post->server, $post->node, $post->nodeid, 'share'])}')">
                    <i class="material-icons">share</i>
                </span>
                <p class="normal">{$c->__('sendto.attach')}</p>
            </li>
        </ul>
    {/if}

    <hr />

    <ul class="list thin">
        <li class="subheader"><p>{$c->__('sendto.contact')}</p></li>
        {loop="$contacts"}
            <li>
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
                <span class="control icon active gray" onclick="SendTo_ajaxSend('{$value->id}', {'uri': '{$uri}'})">
                    <i class="material-icons">send</i>
                </span>
                <p class="normal line">{$value->truename}</p>
            </li>
        {/loop}
    </ul>
</section>
