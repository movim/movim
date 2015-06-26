<ul class="divided active spaced">
    <li class="subheader">{$c->__('adhoc.title')}</li>
    {loop="$list"}
        <li class="action" data-node="{$value->attributes()->node}" data-jid="{$value->attributes()->jid}">
            <span class="icon gray">
                <i class="zmdi {$c->getIcon((string)$value->attributes()->node)}"></i>
            </span>
            <div class="action">
                <i class="zmdi zmdi-chevron-right"></i>
            </div>
            <span>{$value->attributes()->name}</span>
        </li>
    {/loop}
</ul>
