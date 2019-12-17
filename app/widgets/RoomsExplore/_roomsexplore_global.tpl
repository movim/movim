{if="!empty($results)"}
    <li class="subheader">
        <p>{$c->__('roomsexplore.global_title')}</p>
    </li>
    {loop="$results"}
        <li onclick="Drawer.clear(); Rooms_ajaxAdd('{$value.jid}', '{$value.name}')"
            title="{$value.jid}">
            <span class="primary icon bubble color {$value.name|stringToColor}">
                {$value.name|firstLetterCapitalize}
            </span>
            <span class="control icon gray">
                <i class="material-icons">add</i>
            </span>

            <p class="line">
                {$value.name}
                <span class="second">{$value.jid}</span>
            </p>
            <p class="line" title="{$value.description}">
                {if="$value.occupants > 0"}
                    <span title="{$c->__('communitydata.sub', $value.occupants)}">
                        {$value.occupants} <i class="material-icons">people</i>
                    </span>
                {/if}
                {if="$value.occupants > 0 && !empty($value.description)"}  – {/if}
                {$value.description}
            </p>
        </li>
    {/loop}
{else}
    <div class="placeholder">
        <i class="material-icons">language</i>
        <h1>{$c->__('roomsexplore.no_global')}</h1>
    </div>
{/if}