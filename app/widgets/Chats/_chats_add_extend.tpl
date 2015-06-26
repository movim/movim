<br />
{$group = ''}
{loop="$contacts"}
    {if="$group != $value->groupname"}
        <li class="subheader">{$value->groupname}</li>
    {/if}
    <li class="condensed" onclick="Chats_ajaxOpen('{$value->jid}'); Dialog.clear()">
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
        <p class="wrap">{$value->jid}</p>
    </li>
    {$group = $value->groupname}
{/loop}
