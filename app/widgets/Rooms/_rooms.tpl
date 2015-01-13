<ul class="thick divided active">
    <li class="subheader">
        {$c->__('chatrooms.title')}
    </li>
    {loop="$conferences"}
        <li data-jid="{$value->conference}"
            data-nick="{$value->nick}"
            class="room {if="$value->status == 1"}online{/if}">
            {if="$value->status == 1"}
                <span class="icon bubble color {$value->name|stringToColor}"><i class="md md-people"></i></span>
            {else}
                <span class="icon bubble color {$value->name|stringToColor}"><i class="md md-people-outline"></i></span>
            {/if}
            <span>{$value->name}</span>
        </li>
    {/loop}
</ul>
