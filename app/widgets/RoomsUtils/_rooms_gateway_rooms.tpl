<div class="select">
    <select onchange="Rooms.selectGatewayRoom(this.value, this.options[this.selectedIndex].label)">
        {loop="$rooms"}
            <option value="{$key}">
                {$name = explodeJid($key)['username']}
                {$value} · {$name}
            </option>
        {/loop}
    </select>
</div>
<label>{$c->__('rooms.gateway_room')}</label>