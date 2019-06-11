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
                <p class="normal line">{$c->__('sendto.attach')}</p>
            </li>
        </ul>
    {/if}

    <hr />

    <ul class="list thin" id="sendto_contacts">
        {autoescape="off"}
            {$c->prepareContacts($contacts, $uri)}
        {/autoescape}
        <br />
        <li onclick="SendTo_ajaxGetMoreContacts('{$uri}')" class="active">
            <span class="control icon active gray">
                <i class="material-icons">expand_more</i>
            </span>
            <p class="normal line center">{$c->__('sendto.more_contacts')}</p>
        </li>
    </ul>
</section>
