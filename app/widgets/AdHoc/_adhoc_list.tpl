<ul class="list middle divided active spaced">
    <li class="subheader">
        <p>{$c->__('adhoc.title')}</p>
    </li>
    {loop="$list"}
        <li data-node="{$value->attributes()->node}" data-jid="{$value->attributes()->jid}">
            <span class="primary icon gray">
                <i class="zmdi {$c->getIcon((string)$value->attributes()->node)}"></i>
            </span>
            <span class="control icon gray">
                <i class="zmdi zmdi-chevron-right"></i>
            </span>
            <p class="normal">{$value->attributes()->name}</p>
        </li>
    {/loop}
</ul>
