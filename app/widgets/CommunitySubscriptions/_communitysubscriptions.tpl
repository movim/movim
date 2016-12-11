<header>
    <ul class="list middle">
        <li>
            <span id="menu" class="primary on_mobile icon active gray" onclick="MovimTpl.toggleMenu()">
                <i class="zmdi zmdi-menu"></i>
            </span>
            <span class="primary icon active gray" onclick="history.back()">
                <i class="zmdi zmdi-arrow-back"></i>
            </span>
            <span class="control icon gray active on_mobile" onclick="MovimTpl.showPanel()">
            </span>
            <p class="center">{$c->__('page.communities')}</p>
            <p class="center">{$c->__('communitysubscriptions.subscriptions')}</p>
        </li>
    </ul>
</header>

{if="$subscriptions == null"}
    <ul class="thick">
        <div class="placeholder icon bookmark">
            <h1>{$c->__('communitysubscriptions.empty_title')}</h1>
            <h4>{$c->__('communitysubscriptions.empty_text1')} {$c->__('communitysubscriptions.empty_text2')}</h4>
        </li>
    </ul>
{else}
    <ul class="list middle flex active all">
        {loop="$subscriptions"}
            {if="$c->checkNewServer($value)"}
                <li class="subheader block large" onclick="MovimUtils.redirect('{$c->route('community', $value->server)}')">
                    <span class="control icon gray"><i class="zmdi zmdi-chevron-right"></i></span>
                    <p>
                        {$value->server} - {$value->servicename}
                    </p>
                </li>
            {/if}
            <li
                class="block"
                onclick="MovimUtils.redirect('{$c->route('community', [$value->server, $value->node])}')"
                title="{$value->server} - {$value->node}"
            >
                {if="$value->logo"}
                    <span class="primary icon bubble">
                        <img src="{$value->getLogo()}">
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
