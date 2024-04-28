<section id="notifications_widget">
    {if="!empty($invitations)"}
    <ul class="list">
        <li class="subheader">
            <div>
                <p>{$c->__('invitations.title')}</p>
            </div>
        </li>
    </ul>
    <ul id="notifications_invitations" class="list middle divided spaced">{loop="$invitations"}<li id="invitation-{$value->jid|cleanupId}" data-jid="{$value->jid}">
        <span class="primary icon bubble active" onclick="MovimUtils.reload('{$c->route('contact', $value->jid)}'); Drawer.clear();">
            <img src="{$value->getPicture()}">
        </span>
        <span class="control icon green active" title="{$c->__('button.accept')}" onclick="Notifications_ajaxAccept('{$value->jid|echapJS}');">
            <i class="material-symbols">check</i>
        </span>
        <span class="control icon red active" title="{$c->__('button.refuse')}" onclick="Notifications_ajaxRefuse('{$value->jid|echapJS}');">
            <i class="material-symbols">close</i>
        </span>
        <div>
            <p class="line normal">
                {$c->__('invitations.wants_to_talk', $value->truename)}
            </p>
            <p class="line">{$value->jid}</p>
        </div>
    </li>{/loop}</ul>
    {/if}

    <div class="placeholder">
        <i class="material-symbols">person_add</i>
        <h4>{$c->__('invitations.no_new')}</h4>
    </div>

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
                        <span class="primary icon bubble">
                            <a href="{$c->route('contact', $value->contact->jid)}">
                                <img src="{$value->contact->getPicture()}">
                            </a>
                        </span>
                    {else}
                        <span class="primary icon bubble color {$value->aid|stringToColor}">
                            <a href="{$c->route('contact', $value->aid)}">
                                {$value->aid|firstLetterCapitalize}
                            </a>
                        </span>
                    {/if}
                    {if="$value->isLike()"}
                        <span class="control icon red">
                            <i class="material-symbols fill">favorite</i>
                        </span>
                    {else}
                        <span class="control icon gray">
                            <i class="material-symbols">comment</i>
                        </span>
                    {/if}

                    <div>
                        <p class="line" onclick="MovimUtils.reload('{$c->route('post', [$parent->server, $parent->node, $parent->nodeid])}'); Drawer.clear();">
                            {$value->truename}
                            {if="$parent->title"}
                                <span class="second">
                                    {$parent->title}
                                </span>
                            {/if}
                        </p>
                        <p class="line" onclick="MovimUtils.reload('{$c->route('post', [$parent->server, $parent->node, $parent->nodeid])}'); Drawer.clear();">
                            <span class="info">{$value->published|strtotime|prepareDate:true,true}</span>
                            {if="!$value->isLike()"}
                                {$c->__('post.commented')}
                                {if="$value->content"}
                                    <span class="second">{$value->content}</span>
                                {/if}
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
            <i class="material-symbols">notifications_none</i>
            <h4>{$c->__('notifs.empty')}</h4>
        </div>
    {/if}
</section>
