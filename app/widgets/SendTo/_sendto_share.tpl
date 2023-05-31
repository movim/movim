<section id="sendto">
    {if="$card"}
        <ul class="list card middle active">
            <li class="subheader">
                <div>
                    <p>{$c->__('button.send_to')}</p>
                </div>
            </li>
            {autoescape="off"}
                {$card}
            {/autoescape}
        </ul>
    {/if}

    {if="$conferences->isNotEmpty()"}
        <ul class="list thin">
            <li class="subheader">
                <div>
                    <p>{$c->__('sendto.chatroom')}</p>
                </div>
            </li>
            {loop="$conferences"}
                <li>
                    <span class="primary icon bubble small"
                        style="background-image: url({$value->getPhoto()});">
                    </span>

                    <span class="control icon active gray" onclick="SendTo_ajaxSend('{$value->conference|echapJS}', {'uri': '{$uri}'}, true, '{$openlink}');">
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

    <ul class="list thin" id="sendto_share_contacts">
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
