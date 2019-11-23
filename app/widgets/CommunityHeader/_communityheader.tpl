{if="$info != null && $info->pubsubpublishmodel != null && $info->pubsubpublishmodel != 'publishers'"}
    {if="$info->pubsubpublishmodel == 'open' || ($info->pubsubpublishmodel == 'subscribers' && $subscription != null)"}
        <a class="button action color" title="{$c->__('menu.add_post')}" href="{$c->route('publish', [$server, $node])}">
            <i class="material-icons">edit</i>
        </a>
    {/if}
{else}
    <a onclick="CommunityHeader_ajaxTestPublish('{$server}', '{$node}')" class="button action color" title="{$c->__('menu.add_post')}">
        <i class="material-icons">edit</i>
    </a>
{/if}
<ul class="list thick">
    <li>
        {if="$c->getUser()->hasPubsub()"}
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
            <i class="material-icons">arrow_back</i>
        </span>
        {if="$info != null"}
            {$url = $info->getPhoto('l')}
            {if="$url"}
                <span class="primary icon bubble">
                    <img src="{$url}"/>
                </span>
            {/if}
        {/if}
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
        <p class="line on_mobile" {if="$info != null && $info->description"}title="{$info->description|strip_tags}"{/if}>
            {if="$num > 0"}
                <i class="material-icons">receipt</i> {$num}
            {/if}
            {if="$info != null"}
                – <i class="material-icons">people</i> {$c->__('communitydata.sub', $info->occupants)}
                {if="$info->description"}
                    – {$info->description|strip_tags}
                {/if}
            {else}
                {$server}
            {/if}
        </p>
        <p class="line on_desktop">
            {$server}
        </p>
    </li>
</ul>
