<br />
{loop="$contacts"}
    {if="$group != $value->groupname"}
        <li class="subheader">{$value->groupname}</li>
    {/if}
    <li class="condensed" onclick="Chats_ajaxOpen('{$value->jid}'); Dialog.clear()">
        <span class="icon bubble">
            <img
                class="avatar"
                src="{$value->getPhoto('s')}"
                alt="avatar"
            />
        </span>
        <span>{$value->getTrueName()}</span>
        <p class="wrap">{$value->jid}</p>
    </li>
    {$group = $value->groupname}
{/loop}
