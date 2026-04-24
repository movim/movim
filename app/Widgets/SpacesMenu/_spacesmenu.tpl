{loop="$spaces"}
    <li {if="$value->space_in"}onclick="SpacesMenu.get('{$value->server}', '{$value->node}', '{$c->route('space', [$value->server, $value->node])}')"
        {else}onclick="SpacesMenu_ajaxLockedMenu('{$value->server}', '{$value->node}')"{/if}
        class="{if="$value->pinned"}pinned{/if} {if="$server && $value->server == $node && $node && $value->node == $node"}enabled{/if}">
        <span class="primary icon bubble space symbol {if="!$value->space_in"}locked{/if}"
            {if="$value->info"}title="{$value->info->name}"{/if}
            {$unreads = $value->spaceUnreads($c->me)}
            id="{$value->counterId}"
            {if="$unreads > 0"}data-counter="{$unreads}"{/if}
            >
            {if="$value->info"}
                <a href="#" onclick="listIconClick(event)">
                    <img src="{$value->info->getPicture(placeholder: $value->info->name)}">
                    <span data-key="space{$value->server}{$value->node}" class="counter notifications"></span>
                </a>
            {else}
                <i class="material-symbols spin">progress_activity</i>
            {/if}
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
        <a href="#" onclick="listIconClick(event)">
            <i class="material-symbols">add</i>
        </a>
    </span>
    <div>
        <p class="line">{$c->__('spacesmenu.add_space_title')}</p>
    </div>
</li>
