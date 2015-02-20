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
                <!--
                <a class="button flat red"
                   onclick="{$c->genCallRefuse($value->jid)}">
                    <i class="fa fa-times"></i>
                </a>
                <a class="button flat"
                   onclick="{$c->genCallAccept($value->jid)}">
                    {$c->__('button.add')}
                </a>
                -->
                <a class="button flat" data-jid="{$value->jid}">
                    {$c->__('notifs.manage')}
                </a>
            </div>
            <span class="icon"><img src="{$value->getPhoto('xs')}"/></span>
            <span href="{$c->route('friend', $value->jid)}">
                {$value->getTrueName()}
            </span>
        </li>
        {/if}
    {/loop}
</ul>
