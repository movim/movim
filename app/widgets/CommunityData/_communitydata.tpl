<br />
{if="$info"}
    <ul class="list block middle">
        {if="$info->logo"}
            <li class="large">
                <p class="center">
                    <img src="{$info->getLogo()}" style="max-width: 100%"/>
                </p>
            </li>
        {/if}
        <li class="large">
            <p class="normal center line" title="{$info->name}">{$info->name}</p>
            {if="$info->description != null && trim($info->description) != ''"}
                <p class="center" title="{$info->description}">{$info->description}</p>
            {/if}
            {if="$info->created"}
                <p class="center">
                    {$info->created|strtotime|prepareDate:true,true}
                </p>
            {/if}
        </li>

        <li>
            <span class="primary icon gray">
                <i class="zmdi zmdi-accounts"></i>
            </span>
            <p class="normal">{$c->__('communitydata.sub', $info->occupants)}</p>
        </li>

        <li>
            <span class="primary icon gray">
                <i class="zmdi zmdi-receipt"></i>
            </span>
            <p class="normal">{$c->__('communitydata.num', $num)}</p>
        </li>
    </ul>

    <ul class="list middle active">
        {if="$info->related"}
            {$related = $info->related}
            <li onclick="MovimUtils.redirect('{$c->route('chat', [$related->server,'room'])}')">
                <span class="primary icon bubble color
                    {$related->name|stringToColor}">
                    {$related->name|firstLetterCapitalize}
                </span>

                <span class="control icon">
                    <i class="zmdi zmdi-chevron-right"></i>
                </span>

                <p class="normal line">{$related->name} <span class="second">{$related->server}</span></p>
                <p class="line"
                    {if="$related->description"}title="{$related->description}"{/if}>

                    {if="$related->occupants > 0"}
                        <span title="{$c->__('communitydata.sub', $related->occupants)}">
                            {$related->occupants} <i class="zmdi zmdi-accounts"></i>  –
                        </span>
                    {/if}
                    {if="$related->description"}
                        {$related->description}
                    {else}
                        {$related->server}
                    {/if}
                </p>
            </li>
        {/if}

        <a href="{$c->route('node', [$info->server, $info->node])}" target="_blank" class="block">
            <li>
                <span class="primary icon">
                    <i class="zmdi zmdi-portable-wifi"></i>
                </span>
                <span class="control icon">
                    <i class="zmdi zmdi-chevron-right"></i>
                </span>
                <p class="normal">{$c->__('communitydata.public')}</p>
            </li>
        </a>
    </ul>
{/if}
