<ul class="list fill active">
    {loop="$list"}
        {if="isset($value->attributes()->name)"}
            <li onclick="AdHoc_ajaxCommand('{$value->attributes()->jid}', '{$value->attributes()->node}')">
                <span class="primary icon gray">
                    <i class="material-symbols">{$c->getIcon((string)$value->attributes()->node)}</i>
                </span>
                <span class="control icon gray">
                    <i class="material-symbols">chevron_right</i>
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
