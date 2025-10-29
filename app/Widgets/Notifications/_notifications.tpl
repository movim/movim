<section id="notifications_widget">
    {if="$subscribePresences->isNotEmpty()"}
        <ul class="list">
            <li class="subheader">
                <div>
                    <p>{$c->__('invitations.received')}</p>
                </div>
            </li>
        </ul>
        <ul id="notifications_invitations" class="list middle spaced">{loop="$subscribePresences"}<li id="invitation-{$value->jid|cleanupId}" data-jid="{$value->jid}">
                <span class="primary icon bubble active" onclick="MovimUtils.reload('{$c->route('contact', $value->jid)}'); Drawer.clear();">
                    <img src="{$value->contact != null   ? $value->contact->getPicture() : $value->getPicture()}">
                </span>
                <span class="control icon green active" title="{$c->__('button.accept')}" onclick="Notifications_ajaxAccept('{$value->jid|echapJS}');">
                    <i class="material-symbols">check</i>
                </span>
                <span class="control icon red active" title="{$c->__('button.refuse')}" onclick="Notifications_ajaxRefuse('{$value->jid|echapJS}');">
                    <i class="material-symbols">close</i>
                </span>
                <div>
                    <p class="line normal">
                        {$c->__('invitations.adds_you', $value->contact != null ? $value->contact->truename : $value->jid)}
                    </p>
                    <p class="line">{$value->jid}</p>
                </div>
            </li>{/loop}</ul>
        <div class="placeholder">
            <i class="material-symbols">person_add</i>
            <h4>{$c->__('invitations.no_new')}</h4>
        </div>
    {/if}

    {if="$subscriptionRoster->isNotEmpty()"}
        <ul class="list">
            <li class="subheader">
                <div>
                    <p>{$c->__('subscription.nil')}</p>
                </div>
            </li>
        </ul>
        <ul id="notifications_subscriptions" class="list middle spaced">{loop="$subscriptionRoster"}<li id="invitation-{$value->jid|cleanupId}" data-jid="{$value->jid}">
                <span class="primary icon bubble active" onclick="MovimUtils.reload('{$c->route('contact', $value->jid)}'); Drawer.clear();">
                    <img src="{$value->getPicture()}">
                </span>
                {if="$value->ask == 'subscribe'"}
                    <span class="control icon gray disabled" title="{$c->__('room.invited')}">
                        <i class="material-symbols">chat_paste_go</i>
                    </span>
                {else}
                    <span class="control icon gray active" title="{$c->__('button.add')}" onclick="Notifications_ajaxAddAsk('{$value->jid|echapJS}'); Drawer.clear();">
                        <i class="material-symbols">add</i>
                    </span>
                {/if}
                <span class="control icon gray active" title="{$c->__('button.delete')}" onclick="Notifications_ajaxDeleteContact('{$value->jid|echapJS}'); Drawer.clear();">
                    <i class="material-symbols">delete</i>
                </span>
                <div>
                    <p class="line normal">
                        {$value->truename}
                    </p>
                    <p class="line">{$value->jid}</p>
                </div>
            </li>{/loop}</ul>
        <div class="placeholder">
            <i class="material-symbols">person_check</i>
            <h4>{$c->__('invitations.no_subscriptions')}</h4>
        </div>
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
                        <span class="primary icon bubble active small"
                            onclick="MovimUtils.reload('{$c->route('contact', $value->contact->jid)}')">
                            <img src="{$value->contact->getPicture()}">
                        </span>
                    {else}
                        <span class="primary icon bubble color {$value->aid|stringToColor} active small"
                            onclick="MovimUtils.reload('{$c->route('contact', $value->aid)}')">
                            {$value->aid|firstLetterCapitalize}
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
                            <span class="info">{$value->published|prepareDate:true,true}</span>
                            {if="!$value->isLike()"}
                                {if="$value->title"}
                                    {$value->title}
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
