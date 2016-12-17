<ul class="list thin active">
    <li class="subheader">
        <p>
            <span class="info">{$notifs|count}</span>
            {$c->__('notifs.title')}
        </p>
    </li>

    {if="$notifs"}
        {loop="$notifs"}
            <a href="{$c->route('post', [$value->origin, 'urn:xmpp:microblog:0', $value->getParent()])}">
                <li>
                    {$url = $value->getContact()->getPhoto('m')}
                    {if="$url"}
                        <span class="primary small icon bubble color white">
                            <img src="{$url}"/>
                        </span>
                    {else}
                        <span class="primary small icon bubble color {$value->getContact()->jid|stringToColor}">
                            <i class="zmdi zmdi-account"></i>
                        </span>
                    {/if}
                    <p class="line">{$value->contentraw}</p>
                    <p class="line">
                        <span class="info">{$value->published|strtotime|prepareDate:true,true}</span>
                        {$value->getContact()->getTrueName()}
                    </p>
                </li>
            </a>
        {/loop}
        <li onclick="Notifs_ajaxClear()">
            <span class="primary icon gray">
                <i class="zmdi zmdi-notifications-off"></i>
            </span>
            <p class="normal">{$c->__('button.clear')}</p>
        </li>
    {else}
        <li class="disabled">
            <span class="primary icon gray">
                <i class="zmdi zmdi-notifications-none"></i>
            </span>
            <p class="normal center">{$c->__('notifs.empty')}</p>
        </li>
    {/if}
</ul>
