<section id="sendto">
    {if="$card"}
        <ul class="list card middle">
            <li class="subheader">
                <div>
                    <p>{$c->__('button.send_to')}</p>
                </div>
            </li>
            {autoescape="off"}
                {$card}
            {/autoescape}
            {if="$c->getUser()->hasPubsub()"}
                <li>
                    <span class="control icon active gray"
                        onclick="MovimUtils.redirect('{$c->route('publish', [$c->getUser()->id, 'urn:xmpp:microblog:0', '', $post->server, $post->node, $post->nodeid])}')">
                        <i class="material-icons">share</i>
                    </span>
                    <div>
                        <p class="normal line">{$c->__('sendto.attach')}</p>
                    </div>
                </li>
            {/if}
        </ul>
    {/if}

    <hr />

    {if="$conferences->isNotEmpty()"}
        <ul class="list thin divided spaced">
            <li class="subheader">
                <div>
                    <p>{$c->__('sendto.chatroom')}</p>
                </div>
            </li>
            {loop="$conferences"}
                <li>
                    {$url = $value->getPhoto()}
                    {if="$url"}
                        <span class="primary icon bubble color small {$value->name|stringToColor}"
                            style="background-image: url({$url});">
                        </span>
                    {else}
                        <span class="primary icon bubble color small {$value->name|stringToColor}">
                            {autoescape="off"}
                                {$value->name|firstLetterCapitalize|addEmojis}
                            {/autoescape}
                        </span>
                    {/if}

                    <span class="control icon active gray" onclick="SendTo_ajaxSend('{$value->conference|echapJS}', {'uri': '{$uri}'}, true, '{$openlink}')">
                        <i class="material-icons">send</i>
                    </span>

                    {$info = $value->info}

                    <div>
                        <p class="normal line">
                            <span title="{$value->conference}">{$value->name}</span>
                            <span class="second" {if="isset($info) && $info->description"}title="{$info->description}"{/if}>
                                {if="isset($info) && $info->description"}
                                    {$info->description}
                                {else}
                                    {$value->conference}
                                {/if}
                            </span>
                        </p>
                    </div>
                </li>
            {/loop}
        </ul>
    {/if}

    <ul class="list divided spaced" id="sendto_contacts">
        {autoescape="off"}
            {$c->prepareContacts($contacts, $uri, $openlink)}
        {/autoescape}
        <br />
        <li onclick="SendTo_ajaxGetMoreContacts('{$uri}')" class="active">
            <span class="control icon gray">
                <i class="material-icons">expand_more</i>
            </span>
            <div>
                <p class="normal line center">{$c->__('sendto.more_contacts')}</p>
            </div>
        </li>
    </ul>
</section>
