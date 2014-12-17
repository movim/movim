<ul>
    {if="$invitations"}
    <li>
        <h2><i class="fa fa-plus-square"></i> {$c->__('notifs.title')}</h2>
    </li>
    {/if}
    {loop="$invitations"}
        {if="isset($value)"}
        <li>
            <a href="{$c->route('friend', $value->jid)}">
                <img src="{$value->getPhoto('xs')}"/> {$value->getTrueName()}
            </a>
            <br />
            <a class="button color red merged right alone oppose"
               onclick="{$c->genCallRefuse($value->jid)}">
                <i class="fa fa-times oppose"></i>
            </a>
            <a class="button color green merged left alone oppose"
               onclick="{$c->genCallAccept($value->jid)}">
                <i class="fa fa-plus oppose"></i> {$c->__('button.add')}
            </a>
        </li>
        {/if}
    {/loop}
</ul>
