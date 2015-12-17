<ul class="list active middle">
    {loop="$contacts"}
        <li onclick="Roster.setFound('{$value->jid}')">
            {$url = $value->getPhoto('s')}
            {if="$url"}
                <span class="primary icon bubble">
                    <img src="{$url}">
                </span>
            {else}
                <span class="primary icon bubble color {$value->jid|stringToColor}">
                    <i class="zmdi zmdi-account"></i>
                </span>
            {/if}
            <p>{$value->getTrueName()}</p>
            <p>{$value->jid}</p>
        </li>
    {/loop}
</ul>
