<div>
    <span class="on_desktop icon"><i class="zmdi zmdi-pages"></i></span>
    <h2>
        {$c->__('page.groups')}
    </h2>
</div>
<div>
    <ul class="active">
        {if="$subscription == null"}
            <li title="{$c->__('group.subscribe')}"
                onclick="Group_ajaxAskSubscribe('{$item->server}', '{$item->node}')">
                <span class="icon">
                    <i class="zmdi zmdi-bookmark-outline"></i>
                </span>
            </li>
        {else}
            <li title="{$c->__('group.unsubscribe')}"
                onclick="Group_ajaxAskUnsubscribe('{$item->server}', '{$item->node}')">
                <span class="icon">
                    <i class="zmdi zmdi-bookmark"></i>
                </span>
            </li>
        {/if}
        {if="$role == 'owner'"}
            <li class="thin show_context_menu">
                <span class="icon">
                    <i class="zmdi zmdi-more-vert"></i>
                </span>
            </li>
        {/if}
    </ul>
    <div class="return active condensed {if="$role == 'owner'"}r2{else}r1{/if}"
        onclick="MovimTpl.hidePanel(); Group_ajaxClear(); Groups_ajaxHeader();">
        <span id="back" class="icon"><i class="zmdi zmdi-arrow-back"></i></span>
        <h2>
            {if="$item != null"}
                {if="$item->name"}
                    {$item->name}
                {else}
                    {$item->node}
                {/if}
            {/if}
        </h2>
        {if="$item->description"}
            <h4 title="{$item->description|strip_tags}">
                {$item->description|strip_tags}
            </h4>
        {else}
            <h4>{$item->server}</h4>
        {/if}
    </div>
    {if="$role == 'owner'"}
        <ul class="simple context_menu active">
            <li onclick="Group_ajaxGetConfig('{$item->server}', '{$item->node}')">
                <span>{$c->__('group.configuration')}</span>
            </li>
            <li onclick="Group_ajaxGetSubscriptions('{$item->server}', '{$item->node}')">
                <span>{$c->__('group.subscriptions')}</span>
            </li>
            <li onclick="Group_ajaxDelete('{$item->server}', '{$item->node}')">
                <span>{$c->__('button.delete')}</span>
            </li>
        </ul>
    {/if}
</div>
