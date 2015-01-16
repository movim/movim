<ul class="active">
    {if="$invitations"}
    <li class="subheader">{$c->__('notifs.title')}</li>
    {/if}
    {loop="$invitations"}
        {if="isset($value)"}
        <li data-jid="{$value->jid}">
            <div class="control">
                <a class="button flat red"
                   onclick="{$c->genCallRefuse($value->jid)}">
                    <i class="fa fa-times"></i>
                </a>
                <a class="button flat"
                   onclick="{$c->genCallAccept($value->jid)}">
                    {$c->__('button.add')}
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
