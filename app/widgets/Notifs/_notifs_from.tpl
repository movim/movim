<ul class="list active all">
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
            <span class="control">
                <a class="button flat" data-jid="{$value->jid}">
                    {$c->__('notifs.manage')}
                </a>
            </span>
            <p class="normal line">
                <a href="{$c->route('contact', $value->jid)}">
                    {$value->getTrueName()}
                </a>
            </p>
        </li>
        {/if}
    {/loop}
</ul>
