{if="$subscriptions->isEmpty()"}
    <ul class="thick">
        <div class="placeholder">
            <i class="material-icons">bookmark</i>
            <h1>{$c->__('communitysubscriptions.empty_title')}</h1>
            <h4>{$c->__('communitysubscriptions.empty_text1')} {$c->__('communitysubscriptions.empty_text2')}</h4>
        </li>
    </ul>
{else}
    <ul class="list middle flex third active all">
        {loop="$subscriptions"}
            {if="$c->checkNewServer($value)"}
                <li class="subheader block large"
                    onclick="MovimUtils.redirect('{$c->route('community', $value->server)}')">
                    <span class="control icon gray">
                        <i class="material-icons">chevron_right</i>
                    </span>
                    <div>
                        <p>{$value->server}</p>
                    </div>
                </li>
            {/if}
            <li
                class="block"
                onclick="MovimUtils.redirect('{$c->route('community', [$value->server, $value->node])}')"
                title="{$value->server} - {$value->node}"
            >
                {$url = false}

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
                        {else}
                            {$value->node}
                        {/if}

                    </p>
                    <p class="line">
                        {if="$value->public"}
                            <span class="tag color gray">{$c->__('room.public_muc')}</span>
                        {/if}
                        {if="$value->info && $value->info->description"}
                            {$value->info->description|strip_tags}
                        {else}
                            {$value->node}
                        {/if}
                    </p>
                </div>
            </li>
        {/loop}
    </ul>
{/if}
