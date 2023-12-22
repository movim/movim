<ul class="list thin">
    <li class="block large">
        <div>
            <p class="center all">
                <img class="avatar" src="{$info->getPicture('l')}"/>
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
                        {$info->description|trim|nl2br|addEmojis|addUrls|addHashtagsLinks}
                    {/autoescape}
                    <br />
                {/if}

                {if="$info->created"}
                    <br />
                    <i class="material-symbols icon-text">calendar</i>
                    {$info->created|strtotime|prepareDate:true,true}
                {/if}

                <br />
                <i class="material-symbols icon-text">article</i>
                {$c->__('communitydata.num', $num)}
                ·
                <i class="material-symbols icon-text">people</i>
                {$c->__('communitydata.sub', $info->occupants)}

                {if="$info->pubsubpublishmodel == 'publishers'"}
                    <br />
                    <i class="material-symbols icon-text">assignment_ind</i>
                    {$c->__('communitydata.publishmodel_publishers')}
                {/if}
                {if="$info->pubsubpublishmodel == 'subscribers'"}
                    <br />
                    <i class="material-symbols icon-text">assignment_turned_in</i>
                    {$c->__('communitydata.publishmodel_subscribers')}
                {/if}
                {if="$info->isGallery()"}
                    <br />
                    <i class="material-symbols icon-text">grid_view</i>
                    {$c->__('communityconfig.type_gallery_title')}
                {/if}

            </p>
        </div>
    </li>
</ul>

<br />
