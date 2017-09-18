<ul class="list divided active spaced">
    {if="!empty($list)"}
    <li class="subheader">
        <p>{$c->__('adhoc.title')}</p>
    </li>
    {/if}
    {loop="$list"}
        {if="isset($value->attributes()->name)"}
            <li data-node="{$value->attributes()->node}" data-jid="{$value->attributes()->jid}">
                <span class="primary icon gray">
                    <i class="zmdi {$c->getIcon((string)$value->attributes()->node)}"></i>
                </span>
                <span class="control icon gray">
                    <i class="zmdi zmdi-chevron-right"></i>
                </span>
                <p class="normal line" title="{$value->attributes()->name}">
                    {$value->attributes()->name}
                </p>
            </li>
        {/if}
    {/loop}
</ul>
