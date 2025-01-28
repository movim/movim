<div class="select">
    <select onchange="Rooms.selectGatewayRoom(this.value, this.options[this.selectedIndex].label)">
        {$group = null}
        {loop="$rooms"}
            {if="$group != $value->parent"}
                {if="$group != null"}
                    </optgroup>
                {/if}
                <optgroup {if="$value->parent != null"} label="{$value->parent}"{/if}>
            {/if}
            <option value="{$key}">
                {$value->name}
            </option>
            {$group = $value->parent}
        {/loop}
        {if="$group != null"}
            </optgroup>
        {/if}
    </select>
</div>
<label>{$c->__('rooms.gateway_room')}</label>
