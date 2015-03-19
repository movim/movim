<ul class="thick divided spaced active">
    <li class="subheader">
        {$c->__('chatrooms.title')}
        <span class="info">{$conferences|count}</span>
    </li>
    {loop="$conferences"}
        {$connected = $c->checkConnected($value->conference, $value->nick)}
        <li data-jid="{$value->conference}" class="condensed"
            {if="$value->nick != null"} data-nick="{$value->nick}" {/if}
            class="room {if="$connected"}online{/if}">
            {if="$connected"}
                <span class="icon bubble color {$value->name|stringToColor}"><i class="md md-people"></i></span>
            {else}
                <span class="icon bubble color {$value->name|stringToColor}"><i class="md md-people-outline"></i></span>
            {/if}
            <span>{$value->name}</span>
            <p>{$value->conference}</p>
        </li>
    {/loop}
</ul>
