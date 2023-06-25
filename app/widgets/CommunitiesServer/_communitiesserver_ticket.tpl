<li
    class="block
        {if="$community->subscription == 'subscribed'"}action{/if}
        {if="$community->occupants > 0"}condensed{/if}
        "
    onclick="MovimUtils.reload('{$c->route('community', [$community->server, $community->node])}')"
    title="{$community->server} - {$community->node}"
>
    {if="$community->subscription == 'subscribed'"}
        <span class="control icon gray">
            <i class="material-icons">bookmark</i>
        </span>
    {/if}

    <span class="primary icon thumb">
        <img src="{$community->getPhoto('m')}"/>
    </span>
    <div>
        <p class="line">
            {if="$community->name"}
                {$community->name}
            {else}
                {$community->node}
            {/if}
        </p>
        {if="$community->description"}
            <p class="line">{$community->description|strip_tags}</p>
        {/if}
        <p class="line">
            <a href="#">{$community->node}</a>
            {if="$community->isGallery()"}
                <i class="material-icons">grid_view</i>
                ·
            {/if}
            {if="$community->occupants > 0"}
                <span title="{$c->__('communitydata.sub', $community->occupants)}">
                    {$community->occupants} <i class="material-icons">people</i>
                </span>
            {/if}
            {if="$community->published"}
                <span class="info">
                    <i class="material-icons">update</i>
                    {$community->published|strtotime|prepareDate:true}
                </span>
            {/if}
        </p>
    </div>
</li>