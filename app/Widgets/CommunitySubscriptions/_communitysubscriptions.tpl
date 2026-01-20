{if="$subscriptions->isEmpty()"}
    <ul class="thick">
        <div class="placeholder">
            <i class="material-symbols">bookmark</i>
            <h1>{$c->__('communitysubscriptions.empty_title')}</h1>
            <h4>{$c->__('communitysubscriptions.empty_text1')} {$c->__('communitysubscriptions.empty_text2')}</h4>
        </li>
    </ul>
{else}
    {$currentServer = null}
    <div class="card shadow">
        {loop="$subscriptions"}
            {if="$value->server != $currentServer"}
                {if="$currentServer != null"}
                    </ul></div>
                {/if}

                <div class="block">
                    <ul class="list flex card third active">
                        <li class="subheader">
                            <div>
                                <p>{$value->server}</p>
                            </div>
                        </li>
            {/if}
            {if="$value->info"}
                {autoescape="off"}
                    {$c->prepareTicket($value->info)}
                {/autoescape}
            {else}
                <li
                    class="block"
                    onclick="MovimUtils.reload('{$c->route('community', [$value->server, $value->node])}')"
                    title="{$value->server} - {$value->node}"
                >
                    <span class="primary icon thumb">
                        <img src="{$value->node|avatarPlaceholder}">
                    </span>
                    <div>
                        <p class="line">{$value->node}</p>
                        <p class="line">{$value->node}</p>
                    </div>
                </li>
            {/if}

            {$currentServer = $value->server}
        {/loop}
        </ul></div>
    </div>
{/if}
