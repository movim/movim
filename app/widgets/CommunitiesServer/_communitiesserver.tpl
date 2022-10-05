<header>
    <ul class="list middle padded_top_bottom">
        <li>
            <span class="primary icon icon gray active" onclick="history.back()">
                <i class="material-icons">arrow_back</i>
            </span>
            {if="is_array($nodes) && count($nodes) > 0"}
                <span class="control icon gray">
                    {$nodes|count}
                </span>
            {/if}
            <div>
                <p>
                    {if="isset($item->name)"}
                        {$item->name}
                    {else}
                        {$c->__('page.communities')}
                    {/if}
                </p>
                <p class="line">{$server}</p>
            </div>
        </li>
    </ul>
</header>
{if="$nodes->isEmpty()"}
    <ul class="thick">
        <div class="placeholder">
            <i class="material-icons">group_work</i>
            <h1>{$c->__('error.oops')}</h1>
            <h4>{$c->__('communitiesserver.empty_server')}</h4>
        </li>
    </ul>
{else}
    <ul class="list middle divided spaced active all flex fill padded_top_bottom">
    {loop="$nodes"}
        <li
            class="block
                {if="$value->subscription == 'subscribed'"}action{/if}
                {if="$value->occupants > 0"}condensed{/if}
                "
            onclick="MovimUtils.redirect('{$c->route('community', [$value->server, $value->node])}')"
            title="{$value->server} - {$value->node}"
        >
            {if="$value->subscription == 'subscribed'"}
                <span class="control icon gray">
                    <i class="material-icons">bookmark</i>
                </span>
            {/if}

            {$url = $value->getPhoto('m')}
            {if="$url"}
                <span class="primary icon bubble">
                    <img src="{$url}"/>
                </span>
            {else}
                <span class="primary icon bubble color {$value->node|stringToColor}">
                    {$value->node|firstLetterCapitalize}
                </span>
            {/if}
            <div>
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
                <p class="line">
                    {if="$value->published"}
                        <i class="material-icons">update</i>
                        {$value->published|strtotime|prepareDate:true}
                        ·
                    {/if}
                    {if="$value->occupants > 0"}
                        <span title="{$c->__('communitydata.sub', $value->occupants)}">
                            {$value->occupants} <i class="material-icons">people</i>
                        </span>
                        ·
                    {/if}
                    {$value->node}
                </p>
            </div>
        </li>
    {/loop}
    </ul>
{/if}
<button onclick="CommunitiesServer_ajaxTestAdd('{$server}')" class="button action color"
    title="{$c->__('communitiesserver.add', $server)}">
    <i class="material-icons">add</i>
</button>
