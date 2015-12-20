<ul class="list active middle">
    {if="$invitations"}
    <li class="subheader">
        <p>
            <span class="info">{$invitations|count}</span>
            {$c->__('notifs.title')}
        </p>
    </li>
    {/if}
    {loop="$invitations"}
        {if="isset($value)"}
        <li data-jid="{$value->jid}" class="action">
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
            <span class="control icon active green" data-jid="{$value->jid}">
                <i class="zmdi zmdi-settings"></i>
            </span>
            <p class="normal line">
                {$value->getTrueName()}
            </p>
        </li>
        {/if}
    {/loop}
</ul>
