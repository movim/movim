<br />

{if="$info"}
    {$url = $info->getPhoto('l')}
    {if="$url"}
        <ul class="list">
            <li class="block large">
                <content>
                    <p class="center">
                        <img class="avatar" src="{$url}"/>
                    </p>
                </content>
            </li>
        </ul>
    {/if}

    <ul class="list block middle flex">
        <li class="block large">
            <content>
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
            </content>
        </li>

        {if="$info->related"}
            {$related = $info->related}
            <li onclick="MovimUtils.redirect('{$c->route('chat', [$related->server,'room'])}')"
                class="block large active">
                <span class="primary icon bubble gray">
                    <i class="material-icons">forum</i>
                </span>

                <span class="control icon gray">
                    <i class="material-icons">chevron_right</i>
                </span>

                <content>
                    <p class="normal line">{$related->name} <span class="second">{$related->server}</span></p>
                    <p class="line"
                        {if="$related->description"}title="{$related->description}"{/if}>

                        {if="$related->occupants > 0"}
                            <span title="{$c->__('communitydata.sub', $related->occupants)}">
                                {$related->occupants} <i class="material-icons">people</i>  ·
                            </span>
                        {/if}
                        {if="$related->description"}
                            {$related->description}
                        {else}
                            {$related->server}
                        {/if}
                    </p>
                </content>
            </li>
        {/if}

        <a href="{$c->route('node', [$info->server, $info->node])}" target="_blank" class="block large">
            <li class="active">
                <span class="primary icon">
                    <i class="material-icons">wifi_tethering</i>
                </span>
                <span class="control icon">
                    <i class="material-icons">chevron_right</i>
                </span>
                <content>
                    <p class="normal">{$c->__('communitydata.public')}</p>
                </content>
            </li>
        </a>
    </ul>
{/if}
