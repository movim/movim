<ul class="list active">
    <li class="subheader">
        <p>
            {$c->__('notifs.title')}
        </p>
    </li>

    {if="$notifs"}
        {$old = null}
        {loop="$notifs"}
            {$parent = $value->getParent()}
            {if="$parent"}
                <a href="{$c->route('post', [$value->origin, 'urn:xmpp:microblog:0', $parent->nodeid])}">
                    <li>
                        {if="$value->isLike()"}
                            <span class="primary icon red small">
                                <i class="zmdi zmdi-favorite"></i>
                            </span>
                        {else}
                            <span class="primary small icon gray">
                                <i class="zmdi zmdi-comment"></i>
                            </span>
                        {/if}
                        <p class="line">
                            {$parent->title}
                            {if="!$value->isLike()"}
                                <span class="second">{$value->contentraw}</span>
                            {/if}
                        </p>
                        <p class="line normal">
                            <span class="info">{$value->published|strtotime|prepareDate:true,true}</span>
                            {$value->getContact()->getTrueName()}
                        </p>
                    </li>
                </a>
                {$old = $parent}
            {/if}
        {/loop}
        <li onclick="Notifs_ajaxClear()">
            <span class="primary icon gray small">
                <i class="zmdi zmdi-format-clear-all"></i>
            </span>
            <p class="normal">{$c->__('button.clear')}</p>
        </li>
    {else}
        <li class="disabled">
            <span class="primary icon gray small">
                <i class="zmdi zmdi-notifications-none"></i>
            </span>
            <p class="normal center">{$c->__('notifs.empty')}</p>
        </li>
    {/if}
</ul>
