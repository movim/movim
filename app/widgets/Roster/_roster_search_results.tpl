<ul class="active">
    {loop="$contacts"}
        <li class="condensed" onclick="Roster.setFound('{$value->jid}')">
            {$url = $value->getPhoto('s')}
            {if="$url"}
                <span class="icon bubble">
                    <img src="{$url}">
                </span>
            {else}
                <span class="icon bubble color {$value->jid|stringToColor}">
                    <i class="zmdi zmdi-account"></i>
                </span>
            {/if}
            <span>{$value->getTrueName()}</span>
            <p>{$value->jid}</p>
        </li>
    {/loop}
</ul>
