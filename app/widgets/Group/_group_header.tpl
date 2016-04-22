<ul class="list middle">
    <li>
        <span id="back" class="primary icon active" onclick="MovimTpl.hidePanel(); Group_ajaxClear();">
            <i class="zmdi zmdi-arrow-back"></i>
        </span>
        {if="$role == 'owner'"}
            <span class="control show_context_menu icon active">
                <i class="zmdi zmdi-more-vert"></i>
            </span>
        {/if}
        {if="$subscription == null"}
            <span class="control icon active" title="{$c->__('group.subscribe')}"
            onclick="Group_ajaxAskSubscribe('{$item->server}', '{$item->node}')">
                <i class="zmdi zmdi-bookmark-outline"></i>
            </span>
        {else}
            <span class="control icon active" title="{$c->__('group.unsubscribe')}"
            onclick="Group_ajaxAskUnsubscribe('{$item->server}', '{$item->node}')">
                <i class="zmdi zmdi-bookmark"></i>
            </span>
        {/if}
        <p class="line">
            {if="$item != null"}
                {if="$item->name"}
                    {$item->name}
                {else}
                    {$item->node}
                {/if}
            {/if}
        </p>
        {if="$item->description"}
            <p class="line" title="{$item->description|strip_tags}">
                {$item->description|strip_tags}
            </p>
        {else}
            <p class="line">{$item->server}</p>
        {/if}
    </li>
</ul>

{if="$role == 'owner'"}
    <ul class="list context_menu active">
        <li onclick="Group_ajaxGetConfig('{$item->server}', '{$item->node}')">
            <p class="normal">{$c->__('group.configuration')}</p>
        </li>
        <li onclick="Group_ajaxGetSubscriptions('{$item->server}', '{$item->node}', true)">
            <p class="normal">{$c->__('group.subscriptions')}</p>
        </li>
        <li onclick="Group_ajaxDelete('{$item->server}', '{$item->node}')">
            <p class="normal">{$c->__('button.delete')}</p>
        </li>
    </ul>
{/if}
