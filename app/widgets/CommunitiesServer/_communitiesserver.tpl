<header>
    <ul class="list middle">
        <li>
            <span class="primary icon icon gray active" onclick="history.back()">
                <i class="zmdi zmdi-arrow-back"></i>
            </span>
            {if="count($nodes) > 0"}
                <span class="control icon gray">
                    {$nodes|count}
                </span>
            {/if}
            <p class="center">{$c->__('page.communities')}</p>
            <p class="center line">{$server}</p>
        </li>
    </ul>
</header>
{if="$nodes == null"}
    <ul class="thick">
        <div class="placeholder icon pages">
            <h1>{$c->__('error.oops')}</h1>
            <h4>{$c->__('communitiesserver.empty_server')}</h4>
        </li>
    </ul>
{else}
    <ul class="list middle divided spaced active all flex">
    {loop="$nodes"}
        <li
            class="block
                {if="$value->subscription == 'subscribed'"}action{/if}
                {if="$value->sub > 0 || $value->num > 0"}condensed{/if}
                "
            onclick="MovimUtils.redirect('{$c->route('community', [$value->server, $value->node])}')"
            title="{$value->server} - {$value->node}"
        >
            {if="$value->subscription == 'subscribed'"}
                <span class="control icon gray">
                    <i class="zmdi zmdi-bookmark"></i>
                </span>
            {/if}

            {if="$value->logo"}
                <span class="primary icon bubble">
                    <img src="{$value->getLogo(50)}">
                </span>
            {else}
                <span class="primary icon bubble color {$value->node|stringToColor}">
                    {$value->node|firstLetterCapitalize}
                </span>
            {/if}
            <p class="line">
                {if="$value->name"}
                    {$value->name}
                {else}
                    {$value->node}
                {/if}
                <span class="second">
                    {if="$value->description"}
                        {$value->description|strip_tags}
                    {/if}
                </span>
            </p>
            <p>
                {if="$value->num > 0"}
                     {$c->__('communitydata.num', $value->num)}
                {/if}
                {if="$value->sub > 0 && $value->num > 0"}
                  -
                {/if}
                {if="$value->sub > 0"}
                    <span title="{$c->__('communitydata.sub', $value->sub)}">
                        {$value->sub} <i class="zmdi zmdi-accounts"></i>
                    </span>
                {/if}
                <span class="info">
                    {$value->published|strtotime|prepareDate:true,true}
                </span>
            </p>
        </li>
    {/loop}
    </ul>
{/if}
<a onclick="CommunitiesServer_ajaxTestAdd('{$server}')" class="button action color">
    <i class="zmdi zmdi-plus"></i>
</a>
