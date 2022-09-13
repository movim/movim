<ul class="list fill divided active spaced">
    {if="!empty($list)"}
    <li class="subheader">
        <div>
            <p>{$c->__('adhoc.title')}</p>
        </div>
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
                <div>
                    <p class="normal line" title="{$value->attributes()->name}">
                        {$value->attributes()->name}
                    </p>
                </div>
            </li>
        {/if}
    {/loop}
</ul>
