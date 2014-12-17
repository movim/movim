<ul class="active">
    {loop="$contacts"}
        <li class="condensed" onclick="Roster.setFound('{$value->jid}')">
            <span class="icon bubble"><img src="{$value->getPhoto('m')}"></span>
            <span>{$value->getTrueName()}</span>
            <p>{$value->jid}</p>
        </li>
    {/loop}
</ul>
