<section id="sendto">
    {if="$card"}
        <ul class="list card middle">
            <li class="subheader">
                <div>
                    <p>{$c->__('button.share')}</p>
                </div>
            </li>
            {autoescape="off"}
                {$card}
            {/autoescape}
            {if="$c->getUser()->hasPubsub()"}
                <li>
                    <span class="control icon active gray"
                        onclick="MovimUtils.redirect('{$c->route('publish', [$post->server, $post->node, $post->nodeid, 'share'])}')">
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
        <ul class="list thin">
            <li class="subheader"><p>{$c->__('sendto.chatroom')}</p></li>
            {loop="$conferences"}
                <li>
                    {$url = $value->getPhoto()}
                    {if="$url"}
                        <span class="primary icon bubble color {$value->name|stringToColor}"
                            style="background-image: url({$url});">
                        </span>
                    {else}
                        <span class="primary icon bubble color {$value->name|stringToColor}">
                            {autoescape="off"}
                                {$value->name|firstLetterCapitalize|addEmojis}
                            {/autoescape}
                        </span>
                    {/if}

                    <span class="control icon active gray" onclick="SendTo_ajaxSend('{$value->conference}', {'uri': '{$uri}'}, true, '{$openlink}')">
                        <i class="material-icons">send</i>
                    </span>

                    {$info = $value->info}

                    <div>
                        <p class="normal line">
                            {$value->name}
                            <span class="second">{$value->conference}</span>
                        </p>
                        <p class="line"
                            {if="isset($info) && $info->description"}title="{$info->description}"{/if}>
                            {if="isset($info) && $info->description"}
                                {$info->description}
                            {else}
                                {$value->conference}
                            {/if}
                        </p>
                    </div>
                </li>
            {/loop}
        </ul>
    {/if}

    <ul class="list thin" id="sendto_contacts">
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
