<ul class="list active">
    <li class="subheader">
        <p>
            {$c->__('notifs.title')}
        </p>
    </li>

    {if="$notifs->isNotEmpty()"}
        {$old = null}
        {loop="$notifs"}
            {$parent = $value->getParent()}
            {if="$parent"}
                <a href="{$c->route('post', [$parent->server, $parent->node, $parent->nodeid])}">
                    <li>
                        {if="$value->isLike()"}
                            <span class="primary icon red">
                                <i class="material-icons">favorite</i>
                            </span>
                        {else}
                            <span class="primary icon gray">
                                <i class="material-icons">comment</i>
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
                            {$value->truename}
                        </p>
                    </li>
                </a>
                {$old = $parent}
            {/if}
        {/loop}
        <li onclick="Notifs_ajaxClear()">
            <span class="primary icon gray">
                <i class="material-icons">clear_all</i>
            </span>
            <p class="normal">{$c->__('button.clear')}</p>
        </li>
    {else}
        <li class="disabled">
            <span class="primary icon gray">
                <i class="material-icons">notifications_none</i>
            </span>
            <p class="normal center">{$c->__('notifs.empty')}</p>
        </li>
    {/if}
</ul>
