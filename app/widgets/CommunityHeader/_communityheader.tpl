<a onclick="CommunityHeader_ajaxTestPublish('{$server}', '{$node}')" class="button action color" title="{$c->__('menu.add_post')}">
    <i class="zmdi zmdi-edit"></i>
</a>
<ul class="list thick">
    <li>
        {if="$c->supported('pubsub')"}
            {if="$subscription == null"}
                <button class="button oppose green color" title="{$c->__('communityheader.subscribe')}"
                onclick="CommunityHeader_ajaxAskSubscribe('{$server|echapJS}', '{$node|echapJS}')">
                    {$c->__('communityheader.subscribe')}
                </button>
            {else}
                <button class="button oppose flat" title="{$c->__('communityheader.unsubscribe')}"
                onclick="CommunityHeader_ajaxAskUnsubscribe('{$server|echapJS}', '{$node|echapJS}')">
                    {$c->__('communityheader.unsubscribe')}
                </button>
            {/if}
        {/if}
        <span class="primary icon active gray" onclick="history.back()">
            <i class="zmdi zmdi-arrow-back"></i>
        </span>
        <p class="line">
            {if="$info != null"}
                {if="$info->name"}
                    {$info->name}
                {else}
                    {$info->node}
                {/if}
            {else}
                {$node}
            {/if}
        </p>
        {if="$info != null && $info->description"}
            <p class="line" title="{$info->description|strip_tags}">
                {$info->description|strip_tags}
            </p>
        {else}
            <p class="line">{$server}</p>
        {/if}
    </li>
</ul>
