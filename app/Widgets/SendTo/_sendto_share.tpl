<section id="sendto">
    {if="$card"}
        {if="$post->isStory()"}
            <ul class="list card shadow flex fourth gallery">
        {else}
            <ul class="list card middle active">
        {/if}
            <li class="subheader">
                <div>
                    <p>{$c->__('button.send_to')}</p>
                </div>
            </li>
            {if="$post->isStory()"}
                <li>
                    <span class="primary icon blue">
                        <i class="material-symbols">info</i>
                    </span>
                    <div>
                        <p>{$c->__('stories.share_title')}</p>
                        <p>{$c->__('stories.share_text')}</p>
                    </div>
                </li>
            {/if}
            {autoescape="off"}
                {$card}
            {/autoescape}
        </ul>
    {/if}

    <ul class="list thick">
        <li>
            <span class="primary icon gray">
                <i class="material-symbols">group</i>
            </span>
            <span class="control icon gray active divided disabled" id="sendto_button"onclick="SendTo.sendToContacts('{$uri}')">
                <i class="material-symbols">send</i>
            </span>
            <div>
                <p class="normal">{$c->__('sendto.pick')}</p>
                <p><span id="sendto_counter">0</span> {$c->__('sendto.selected')}</p>
            </div>
        </li>
    </ul>

    {if="$conferences->isNotEmpty()"}
        <ul class="list thin">
            {loop="$conferences"}
                <li>
                    <span class="primary icon bubble small">
                        <img src="{$value->getPicture()}">
                    </span>

                    <!--<span class="control icon active gray" onclick="SendTo_ajaxSend('{$value->conference|echapJS}', true, '{$uri}');">
                        <i class="material-symbols">send</i>
                    </span>-->
                    <span class="control icon active gray divided share" onclick="SendTo.toggleSend(this)" data-jid="{$value->conference|echapJS}" data-muc="true">
                        <i class="material-symbols"></i>
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
            {$c->prepareContacts($contacts, $uri)}
        {/autoescape}
        <br />
        <li onclick="SendTo_ajaxGetMoreContacts('{$uri}')" class="active">
            <span class="control icon gray">
                <i class="material-symbols">expand_more</i>
            </span>
            <div>
                <p class="normal line center">{$c->__('sendto.more_contacts')}</p>
            </div>
        </li>
    </ul>
</section>
