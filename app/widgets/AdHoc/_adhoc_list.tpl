<ul class="divided active">
    {loop="$list"}
        <li class="action" data-node="{$value->attributes()->node}" data-jid="{$value->attributes()->jid}">
            <span class="icon gray">
                <i class="md {$c->getIcon((string)$value->attributes()->node)}"></i>
            </span>
            <div class="action">
                <i class="md md-chevron-right"></i>
            </div>
            <span>{$value->attributes()->name}</span>
        </li>
    {/loop}
</ul>
