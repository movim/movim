<section id="sendto">
    {if="$card"}
        <ul class="list card middle active">
            <li class="subheader">
                <div>
                    <p>{$c->__('button.share')}</p>
                </div>
            </li>
            {autoescape="off"}
                {$card}
            {/autoescape}
        </ul>
    {/if}

    {if="$osshare && $openlink"}
        <ul class="list middle">
            <li>
                <span class="primary icon bubble gray">
                    <i class="material-icons">share</i>
                </span>
                <span class="control icon active gray divided" onclick="SendTo_ajaxOsShare({$post->id})">
                    <i class="material-icons">ios_share</i>
                </span>
                <div>
                    <p class="line normal">{$c->__('sendto.os_share')}</p>
                </div>
            </li>
        </ul>
    {/if}

    <ul class="list">
        {if="$c->getUser()->hasPubsub()"}
            <li class="subheader">
                <div>
                    <p>{$c->__('sendto.share')}</p>
                </div>
            </li>
            <li>
                <span class="primary icon bubble">
                    <img src="{$me->getPicture()}">
                </span>
                <span class="control icon active gray divided"
                    onclick="MovimUtils.reload('{$c->route('publish', [$c->getUser()->id, 'urn:xmpp:microblog:0', '', $post->server, $post->node, $post->nodeid])}'); Drawer.clear()">
                    <i class="material-icons">post_add</i>
                </span>
                <div>
                    <p class="normal line">{$me->truename}</p>
                </div>
            </li>
        {/if}
    </ul>

    <ul class="list middle">
        <li class="subheader">
            <div>
                <p>{$c->__('communitysubscriptions.subscriptions')}</p>
            </div>
        </li>
        {loop="$subscriptions"}
            <li
                class="block"
                title="{$value->server} - {$value->node}"
            >
                {if="$value->info"}
                    <span class="primary icon bubble">
                        <img src="{$value->info->getPicture('m')}"/>
                    </span>
                {else}
                    <span class="primary icon bubble color {$value->node|stringToColor}">
                        {$value->node|firstLetterCapitalize}
                    </span>
                {/if}
                <span class="control icon active gray divided" onclick="MovimUtils.reload('{$c->route('publish', [$value->server, $value->node, '', $post->server, $post->node, $post->nodeid])}'); Drawer.clear()">
                    <i class="material-icons">post_add</i>
                </span>
                <div>
                    <p class="line normal">
                        {if="$value->info && $value->info->name"}
                            {$value->info->name}
                        {else}
                            {$value->node}
                        {/if}

                    </p>
                    <p class="line">
                        {if="$value->public"}
                            <span class="tag color gray">{$c->__('room.public_muc')}</span>
                        {/if}
                        {if="$value->info && $value->info->description"}
                            {$value->info->description|strip_tags}
                        {else}
                            {$value->node}
                        {/if}
                    </p>
                </div>
            </li>
        {/loop}
    </ul>
</section>