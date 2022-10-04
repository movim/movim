<br />

{if="$info"}
    {autoescape="off"}
        {$c->prepareCard($info)}
    {/autoescape}

    <ul class="list card middle flex">
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

                <div>
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
                </div>
            </li>
        {/if}

        <a href="{$c->route('node', [$info->server, $info->node])}" target="_blank" class="block large">
            <li class="active">
                <span class="primary icon">
                    <i class="material-icons">open_in_new</i>
                </span>
                <span class="control icon">
                    <i class="material-icons">chevron_right</i>
                </span>
                <div>
                    <p class="normal">{$c->__('communitydata.public')}</p>
                </div>
            </li>
        </a>
    </ul>
{/if}
