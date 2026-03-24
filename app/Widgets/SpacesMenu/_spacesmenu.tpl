{loop="$spaces"}
    <li {if="$value->space_in"}onclick="SpacesMenu.get('{$value->server}', '{$value->node}', '{$c->route('space', [$value->server, $value->node])}')"
        class="{if="$value->pinned"}pinned{/if} {if="$node && $value->node == $node"}enabled{/if}"
        {else}onclick="SpacesMenu_ajaxLockedMenu('{$value->server}', '{$value->node}')"{/if}>
        <span class="primary icon bubble space symbol {if="!$value->space_in"}locked{/if}"
            {if="$value->info"}title="{$value->info->name}"{/if}
            {$unreads = $value->spaceUnreads($c->me)}
            id="{$value->counterId}"
            {if="$unreads > 0"}data-counter="{$unreads}"{/if}
            >
            <a href="#" onclick="listIconClick(this)">
                {if="$value->info"}
                    <img src="{$value->info->getPicture(placeholder: $value->info->name)}">
                {else}
                    <i class="material-symbols spin">progress_activity</i>
                {/if}
                <span data-key="space{$value->server}{$value->node}" class="counter notifications"></span>
            </a>
        </span>

        <span class="control icon gray">
            <i class="material-symbols">communities</i>
        </span>
        <div>
            <p class="line">
                {if="$value->info"}
                    {$value->info->name}
                {else}
                    {$c->__('spacesmenu.loading_space_info')}
                {/if}
            </p>
        </div>
    </li>
{/loop}

<li onclick="SpacesMenu_ajaxAdd()">
    <span class="primary icon bubble space_add">
        <a href="#" onclick="listIconClick(this)">
            <i class="material-symbols">add</i>
        </a>
    </span>
    <div>
        <p class="line">{$c->__('spacesmenu.create_space_title')}</p>
    </div>
</li>
