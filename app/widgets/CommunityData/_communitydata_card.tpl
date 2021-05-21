{$url = null}
{$url = $info->getPhoto('l')}
{if="$url"}
    <ul class="list">
        <li class="block large">
            <div>
                <p class="center">
                    <img class="avatar" src="{$url}"/>
                </p>
            </div>
        </li>
    </ul>
{/if}

<ul class="list block middle flex">
    <li class="block large">
        <div>
            <p class="normal center line" title="{$info->name}">
                {if="$info->name"}
                    {$info->name}
                {else}
                    {$info->node}
                {/if}
            </p>
            {if="$info->description != null && trim($info->description) != ''"}
                <p class="center" title="{$info->description}">{$info->description}</p>
            {/if}
            {if="$info->created"}
                <p class="center">
                    {$info->created|strtotime|prepareDate:true,true}
                </p>
            {/if}
            <p class="center">
                <i class="material-icons">receipt</i> {$c->__('communitydata.num', $num)}
                ·
                <i class="material-icons">people</i> {$c->__('communitydata.sub', $info->occupants)}
            </p>
            {if="$info->pubsubpublishmodel == 'publishers'"}
                <p class="center">
                    <i class="material-icons">assignment_ind</i> {$c->__('communitydata.publishmodel_publishers')}
                </p>
            {/if}
            {if="$info->pubsubpublishmodel == 'subscribers'"}
                <p class="center">
                    <i class="material-icons">assignment_turned_in</i> {$c->__('communitydata.publishmodel_subscribers')}
                </p>
            {/if}
        </div>
    </li>
</ul>
