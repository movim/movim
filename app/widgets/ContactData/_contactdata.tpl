<br />
{$url = $contact->getPhoto('m')}

<li class="block large">
    <p class="center">
        {if="$url"}
            <img src="{$url}" style="max-width: 100%">
            <br />
        {/if}
        {$contact->getTrueName()}
    </p>
    {if="$contact->email != null"}
        <p class="center"><img src="{$contact->getPhoto('email')}"/></p>
    {/if}
    {if="$contact->description != null && trim($contact->description) != ''"}
        <p class="all center">{$contact->description}</p>
    {/if}
</li>

{if="count($subscriptions) > 0"}
    <ul class="list active middle large">
        <li class="subheader large">
            <p>
                <span class="info">{$subscriptions|count}</span>
                {$c->__('page.communities')}
            </p>
        </li>
        {loop="$subscriptions"}
            <li class="block"
                title="{$value->server} - {$value->node}"
                onclick="MovimUtils.redirect('{$c->route('community', [$value->server, $value->node])}')">
                {if="$value->logo"}
                    <span class="primary icon bubble">
                        <img src="{$value->getLogo(50)}">
                    </span>
                {else}
                    <span class="primary icon bubble color {$value->node|stringToColor}">{$value->node|firstLetterCapitalize}</span>
                {/if}
                <span class="control icon gray">
                    <i class="zmdi zmdi-chevron-right"></i>
                </span>
                <p class="line normal">
                    {if="$value->name"}
                        {$value->name}
                    {else}
                        {$value->node}
                    {/if}
                </p>
                {if="$value->description"}
                    <p class="line">{$value->description|strip_tags}</p>
                {/if}
            </li>
        {/loop}
    </ul>
{/if}

