<div class="select">
    <select onchange="Rooms.selectGatewayRoom(this.value, this.options[this.selectedIndex].label)">
        {loop="$rooms"}
            <option value="{$key}">
                {$value}
            </option>
        {/loop}
    </select>
</div>
<label>{$c->__('rooms.gateway_room')}</label>