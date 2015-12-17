<br />
{$group = ''}
{loop="$contacts"}
    {if="$group != $value->groupname"}
        <li class="subheader">
            <p>{$value->groupname}</p>
        </li>
    {/if}
    <li onclick="Chats_ajaxOpen('{$value->jid}'); Dialog.clear()">
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
        <p class="line">{$value->getTrueName()}</p>
        <p class="line">{$value->jid}</p>
    </li>
    {$group = $value->groupname}
{/loop}
