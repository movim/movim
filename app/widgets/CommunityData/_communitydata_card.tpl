{$url = null}
{$url = $info->getPhoto('l')}
<ul class="list thin">
    <li class="block large">
        <div>
            <p class="center all">
                {if="$url"}
                    <img class="avatar" src="{$url}"/>
                {else}
                    <span class="avatar icon color {$info->node|stringToColor}">
                        {$info->node|firstLetterCapitalize}
                    </span>
                {/if}
            </p>
        </div>
    </li>
</ul>

<ul class="list middle flex">
    <li class="block large">
        <div>
            <p class="normal center line" title="{$info->name}">
                {if="$info->name"}
                    {$info->name}
                {else}
                    {$info->node}
                {/if}
            </p>
            <p class="center all">
                {if="$info->description != null && trim($info->description) != ''"}
                    {autoescape="off"}
                        {$info->description|trim|nl2br|addEmojis}
                    {/autoescape}
                    <br />
                {/if}

                {if="$info->created"}
                    <br />
                    <i class="material-icons icon-text">calendar</i>
                    {$info->created|strtotime|prepareDate:true,true}
                {/if}

                <br />
                <i class="material-icons icon-text">receipt</i>
                {$c->__('communitydata.num', $num)}
                ·
                <i class="material-icons icon-text">people</i>
                {$c->__('communitydata.sub', $info->occupants)}

                {if="$info->pubsubpublishmodel == 'publishers'"}
                    <br />
                    <i class="material-icons icon-text">assignment_ind</i>
                    {$c->__('communitydata.publishmodel_publishers')}
                {/if}
                {if="$info->pubsubpublishmodel == 'subscribers'"}
                    <br />
                    <i class="material-icons icon-text">assignment_turned_in</i>
                    {$c->__('communitydata.publishmodel_subscribers')}
                {/if}
            </p>
        </div>
    </li>
</ul>

<br />
