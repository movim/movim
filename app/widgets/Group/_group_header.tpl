<div>
    <span class="on_desktop icon"><i class="md md-pages"></i></span>
    <h2>
        {$c->__('page.groups')}
    </h2>
</div>
<div>
    <ul class="active">
        {if="$role == 'owner'"}
            <li onclick="Group_ajaxGetConfig('{$item->server}', '{$item->node}')">
                <span class="icon">
                    <i class="md md-settings"></i>
                </span>
            </li>
        {/if}
        {if="$subscription == null"}
            <li onclick="Group_ajaxAskSubscribe('{$item->server}', '{$item->node}')">
                <span class="icon">
                    <i class="md md-bookmark-outline"></i>
                </span>
            </li>
        {else}
            <li onclick="Group_ajaxAskUnsubscribe('{$item->server}', '{$item->node}')">
                <span class="icon">
                    <i class="md md-bookmark"></i>
                </span>
            </li>
        {/if}
    </ul>
    <h2 class="active {if="$role == 'owner'"}r2{else}r1{/if}"
        onclick="MovimTpl.hidePanel(); Group_ajaxClear(); Groups_ajaxHeader();">
        <span id="back" class="icon"><i class="md md-arrow-back"></i></span>
        {if="$item != null"}
            {if="$item->name"}
                {$item->name}
            {else}
                {$item->node}
            {/if}
        {/if}
    </h2>
</div>
