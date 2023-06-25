{if="$subscriptions->isEmpty()"}
    <ul class="thick fill">
        <div class="placeholder">
            <i class="material-icons">bookmark</i>
            <h1>{$c->__('communitysubscriptions.empty_title')}</h1>
            <h4>{$c->__('communitysubscriptions.empty_text1')} {$c->__('communitysubscriptions.empty_text2')}</h4>
        </li>
    </ul>
{else}
    <ul class="list flex third fill card shadow active">
        {loop="$subscriptions"}
            {if="$c->checkNewServer($value)"}
                <li class="subheader block large active"
                    onclick="MovimUtils.reload('{$c->route('community', $value->server)}')">
                    <span class="primary icon">
                        <i class="material-icons">view_agenda</i>
                    </span>
                    <span class="control icon gray">
                        <i class="material-icons">chevron_right</i>
                    </span>
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
                        <p class="line normal">{$value->node}</p>
                        <p class="line">{$value->node}</p>
                    </div>
                </li>
            {/if}
        {/loop}
    </ul>
{/if}
