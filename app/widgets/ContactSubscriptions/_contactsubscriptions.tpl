{if="count($subscriptions) > 0"}
    <ul class="list active large">
        <li class="subheader large">
            <div>
                <p>
                    <span class="info">{$subscriptions|count}</span>
                    {$c->__('page.communities')}
                </p>
            </div>
        </li>
        {loop="$subscriptions"}
            <a href="{$c->route('community', [$value->server, $value->node])}">
                <li title="{$value->server} - {$value->node}">
                    {$url = null}
                    {if="$value->info"}
                        {$url = $value->info->getPhoto('m')}
                    {/if}

                    {if="$url"}
                        <span class="primary icon bubble">
                            <img src="{$url}"/>
                        </span>
                    {else}
                        <span class="primary icon bubble color {$value->node|stringToColor}">
                            {$value->node|firstLetterCapitalize}
                        </span>
                    {/if}
                    <span class="control icon gray">
                        <i class="material-icons">chevron_right</i>
                    </span>
                    <div>
                        <p class="line normal">
                            {if="$value->info && $value->info->name"}
                                {$value->info->name}
                            {elseif="$value->name"}
                                {$value->name}
                            {else}
                                {$value->node}
                            {/if}
                        </p>
                        {if="$value->info && $value->info->description"}
                            <p class="line">{$value->info->description|strip_tags}</p>
                        {/if}
                    </div>
                </li>
            </a>
        {/loop}
    </ul>
{/if}