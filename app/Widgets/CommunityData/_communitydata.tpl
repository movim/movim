<br />

{if="$info"}
    {autoescape="off"}
        {$c->prepareCard($info)}
    {/autoescape}

    <ul class="list card middle flex shadow">
        {if="$info->related"}
            {$related = $info->related}
            <li onclick="MovimUtils.reload('{$c->route('chat', [$related->server,'room'])}')"
                class="block large active">
                <span class="primary icon bubble gray">
                    <i class="material-symbols">forum</i>
                </span>

                <span class="control icon gray">
                    <i class="material-symbols">chevron_right</i>
                </span>

                <div>
                    <p class="normal line">{$related->name} <span class="second">{$related->server}</span></p>
                    <p class="line"
                        {if="$related->description"}title="{$related->description}"{/if}>

                        {if="$related->occupants > 0"}
                            <span title="{$c->__('communitydata.sub', $related->occupants)}">
                                {$related->occupants} <i class="material-symbols">people</i>  ·
                            </span>
                        {/if}
                        {if="$related->description"}
                            {$related->description|trim|nl2br|addEmojis}
                        {else}
                            {$related->server}
                        {/if}
                    </p>
                </div>
            </li>
        {/if}

        <li class="block large">
            <span class="primary icon gray">
                <i class="material-symbols">globe</i>
            </span>
            <span class="control icon active" onclick="Preview.copyToClipboard('{$c->route('community', [$info->server, $info->node])}')">
                <i class="material-symbols">content_copy</i>
            </span>
            <div>
                <p class="normal">{$c->__('communitydata.public')}</p>
                <p class="line">{$c->route('community', [$info->server, $info->node])}</p>
            </div>
        </li>
        <li class="block large">
            <span class="primary icon orange">
                <i class="material-symbols">rss_feed</i>
            </span>
            <span class="control icon active" onclick="Preview.copyToClipboard('{$c->route('feed', [$info->server, $info->node])}')">
                <i class="material-symbols">content_copy</i>
            </span>
            <div>
                <p class="normal">Atom</p>
            </div>
        </li>
    </ul>
{/if}
