<li
    class="block
        {if="$community->subscription == 'subscribed'"}action{/if}
        {if="$community->occupants > 0"}condensed{/if}
        "
    id="{$id}"
    onclick="MovimUtils.reload('{$c->route('community', [$community->server, $community->node])}')"
    title="{$community->server} - {$community->node}"
>
    {if="$community->subscription == 'subscribed'"}
        <span class="control icon gray">
            <i class="material-symbols">bookmark</i>
        </span>
    {/if}

    <span class="primary icon thumb">
        <img loading="lazy" src="{$community->getPicture(\Movim\ImageSize::M)}"/>
    </span>
    <div>
        <p class="line two normal">
            {if="$community->name"}
                {$community->name}
            {else}
                {$community->node}
            {/if}
        </p>
        {if="$community->description"}
            <p class="line two">{$community->description|strip_tags}</p>
        {/if}
        <p class="line">
            <a href="#">{$community->node}</a>
            {if="$community->isGallery()"}
                <i class="material-symbols">grid_view</i>
                •
            {/if}
            {if="$community->occupants > 0"}
                <span title="{$c->__('communitydata.sub', $community->occupants)}">
                    {$community->occupants} <i class="material-symbols">people</i>
                </span>
            {/if}
            {if="$community->published"}
                <span class="info">
                    <i class="material-symbols">update</i>
                    {$community->published|prepareDate:true}
                </span>
            {/if}
        </p>
    </div>
</li>
