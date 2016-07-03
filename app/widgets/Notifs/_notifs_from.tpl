{if="$invitations"}
<ul class="list">
    <li class="subheader">
        <p>
            <span class="info">{$invitations|count}</span>
            {$c->__('notifs.title')}
        </p>
    </li>
</ul>
{/if}
<ul class="list middle card stacked shadow flex">
    {loop="$invitations"}
        {if="isset($value)"}
        <li data-jid="{$value->jid}" class="block large">
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
            <span class="control icon green active" title="{$c->__('button.accept')}" onclick="Notifs_ajaxAccept('{$value->jid}');">
                <i class="zmdi zmdi-check"></i>
            </span>
            <span class="control icon red active" title="{$c->__('button.refuse')}" onclick="Notifs_ajaxRefuse('{$value->jid}');">
                <i class="zmdi zmdi-close"></i>
            </span>
            <span class="control icon gray active" onclick="Contact_ajaxGetContact('{$value->jid}');">
                <i class="zmdi zmdi-account"></i>
            </span>
            <p class="line normal">
                {$c->__('notifs.wants_to_talk', $value->getTrueName())}
            </p>
            <p>{$value->jid}</p>
        </li>
        {/if}
    {/loop}
</ul>
