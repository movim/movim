<ul class="active all">
    {if="$invitations"}
    <li class="subheader">
        {$c->__('notifs.title')}
        <span class="info">{$invitations|count}</span>
    </li>
    {/if}
    {loop="$invitations"}
        {if="isset($value)"}
        <li data-jid="{$value->jid}" class="action">
            <div class="action">
                <a class="button flat" data-jid="{$value->jid}">
                    {$c->__('notifs.manage')}
                </a>
            </div>
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
            <span href="{$c->route('contact', $value->jid)}">
                {$value->getTrueName()}
            </span>
        </li>
        {/if}
    {/loop}
</ul>
