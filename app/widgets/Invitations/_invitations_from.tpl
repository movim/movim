{if="!empty($invitations)"}
<ul class="list">
    <li class="subheader">
        <p>
            <span class="info">{$invitations|count}</span>
            {$c->__('invitations.title')}
        </p>
    </li>
</ul>
<ul class="list middle divided spaced flex">
    {loop="$invitations"}
        <li data-jid="{$value->jid}" class="block">
            {$url = $value->getPhoto('s')}
            {if="$url"}
                <span class="primary icon bubble">
                    <img src="{$url}">
                </span>
            {else}
                <span class="primary icon bubble color {$value->jid|stringToColor}">
                    <i class="material-icons">person</i>
                </span>
            {/if}
            <span class="control icon green active" title="{$c->__('button.accept')}" onclick="Invitations_ajaxAccept('{$value->jid}');">
                <i class="material-icons">check</i>
            </span>
            <span class="control icon red active" title="{$c->__('button.refuse')}" onclick="Invitations_ajaxRefuse('{$value->jid}');">
                <i class="material-icons">close</i>
            </span>
            <span class="control icon gray active" onclick="MovimUtils.redirect('{$c->route('contact', $value->jid)}')">
                <i class="material-icons">person</i>
            </span>
            <p class="line normal">
                {$c->__('invitations.wants_to_talk', $value->truename)}
            </p>
            <p class="line">{$value->jid}</p>
        </li>
    {/loop}
</ul>
{/if}
