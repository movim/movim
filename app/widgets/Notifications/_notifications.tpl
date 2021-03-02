<section id="notifications_widget">
    {if="!empty($invitations)"}
    <ul class="list">
        <li class="subheader">
            <div>
                <p>
                    <span class="info">{$invitations|count}</span>
                    {$c->__('invitations.title')}
                </p>
            </div>
        </li>
    </ul>
    <ul class="list middle divided spaced">
        {loop="$invitations"}
            <li data-jid="{$value->jid}">
                {$url = $value->getPhoto()}
                {if="$url"}
                    <span class="primary icon bubble">
                        <img src="{$url}">
                    </span>
                {else}
                    <span class="primary icon bubble color {$value->jid|stringToColor}">
                        <i class="material-icons">person</i>
                    </span>
                {/if}
                <span class="control icon green active" title="{$c->__('button.accept')}" onclick="Notifications_ajaxAccept('{$value->jid|echapJS}');">
                    <i class="material-icons">check</i>
                </span>
                <span class="control icon red active" title="{$c->__('button.refuse')}" onclick="Notifications_ajaxRefuse('{$value->jid|echapJS}');">
                    <i class="material-icons">close</i>
                </span>
                <span class="control icon gray active" onclick="MovimUtils.redirect('{$c->route('contact', $value->jid)}')">
                    <i class="material-icons">person</i>
                </span>
                <div>
                    <p class="line normal">
                        {$c->__('invitations.wants_to_talk', $value->truename)}
                    </p>
                    <p class="line">{$value->jid}</p>
                </div>
            </li>
        {/loop}
    </ul>
    {/if}

    {if="$notifs->isNotEmpty()"}
    <ul class="list active">
        <li class="subheader">
            <div>
                <p>{$c->__('notifs.title')}</p>
            </div>
        </li>

        {$delimiter = false}
        {loop="$notifs"}
            {$parent = $value->parent}
            {if="$parent"}
                {if="!$delimiter && strtotime($value->published) < strtotime($since)"}
                    {$delimiter = true}
                    {if="$key != 0"}
                        <li><br/><hr /><br/></li>
                    {/if}
                {/if}
                <li>
                    {if="$value->contact"}
                        {$url = $value->contact->getPhoto('s')}
                        {if="$url"}
                            <span class="primary icon bubble">
                                <a href="{$c->route('contact', $value->contact->jid)}">
                                    <img src="{$url}">
                                </a>
                            </span>
                        {else}
                            <span class="primary icon bubble color {$value->contact->jid|stringToColor}">
                                <a href="{$c->route('contact', $value->contact->jid)}">
                                    <i class="material-icons">person</i>
                                </a>
                            </span>
                        {/if}
                    {else}
                        <span class="primary icon bubble color {$value->aid|stringToColor}">
                            <a href="{$c->route('contact', $value->aid)}">
                                <i class="material-icons">person</i>
                            </a>
                        </span>
                    {/if}
                    {if="$value->isLike()"}
                        <span class="control icon red">
                            <i class="material-icons">favorite</i>
                        </span>
                    {else}
                        <span class="control icon gray">
                            <i class="material-icons">comment</i>
                        </span>
                    {/if}

                    <div>
                        <p class="line" onclick="MovimUtils.redirect('{$c->route('post', [$parent->server, $parent->node, $parent->nodeid])}')">
                            {$value->truename}
                            <span class="second">
                                {$parent->title}
                            </span>
                        </p>
                        <p class="line" onclick="MovimUtils.redirect('{$c->route('post', [$parent->server, $parent->node, $parent->nodeid])}')">
                            <span class="info">{$value->published|strtotime|prepareDate:true,true}</span>
                            {if="!$value->isLike()"}
                                {$c->__('post.commented')}<span class="second">{$value->title}</span>
                            {else}
                                {$c->__('post.liked')}
                            {/if}
                        </p>
                    </div>
                </li>
            {/if}
        {/loop}
    </ul>
    {/if}

    {if="$notifs->isEmpty() && empty($invitations)"}
        <div class="placeholder">
            <i class="material-icons">notifications_none</i>
            <h4>{$c->__('notifs.empty')}</h4>
        </div>
    {/if}
</section>
