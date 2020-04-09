<ul class="list divided active spaced">
    {if="!empty($list)"}
    <li class="subheader">
        <content>
            <p>{$c->__('adhoc.title')}</p>
        </content>
    </li>
    {/if}
    {loop="$list"}
        {if="isset($value->attributes()->name)"}
            <li data-node="{$value->attributes()->node}" data-jid="{$value->attributes()->jid}">
                <span class="primary icon gray">
                    <i class="material-icons">{$c->getIcon((string)$value->attributes()->node)}</i>
                </span>
                <span class="control icon gray">
                    <i class="material-icons">chevron_right</i>
                </span>
                <content>
                    <p class="normal line" title="{$value->attributes()->name}">
                        {$value->attributes()->name}
                    </p>
                </content>
            </li>
        {/if}
    {/loop}
</ul>
