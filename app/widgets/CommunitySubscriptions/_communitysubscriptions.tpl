{if="$subscriptions->isEmpty()"}
    <ul class="thick">
        <div class="placeholder">
            <i class="material-icons">bookmark</i>
            <h1>{$c->__('communitysubscriptions.empty_title')}</h1>
            <h4>{$c->__('communitysubscriptions.empty_text1')} {$c->__('communitysubscriptions.empty_text2')}</h4>
        </li>
    </ul>
{else}
    <ul class="list middle flex active all">
        {loop="$subscriptions"}
            {if="$c->checkNewServer($value)"}
                <li class="subheader block large"
                    onclick="MovimUtils.redirect('{$c->route('community', $value->server)}')">
                    <span class="control icon gray">
                        <i class="material-icons">chevron_right</i>
                    </span>
                    <p>
                        {$value->server}
                    </p>
                </li>
            {/if}
            <li
                class="block"
                onclick="MovimUtils.redirect('{$c->route('community', [$value->server, $value->node])}')"
                title="{$value->server} - {$value->node}"
            >
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
                <p class="line normal">
                    {if="$value->info && $value->info->name"}
                        {$value->info->name}
                    {else}
                        {$value->node}
                    {/if}
                </p>
                {if="$value->info && $value->info->description"}
                    <p class="line">{$value->info->description|strip_tags}</p>
                {/if}
            </li>
        {/loop}
    </ul>
{/if}
