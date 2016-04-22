<header>
    <ul class="list middle">
        <li>
            <span id="menu" class="primary on_mobile icon active gray" onclick="MovimTpl.toggleMenu()">
                <i class="zmdi zmdi-menu"></i>
            </span>
            <span class="primary icon on_desktop icon gray">
                <i class="zmdi zmdi-bookmark"></i>
            </span>
            {if="count($subscriptions) > 0"}
                <span class="control icon gray">
                    {$subscriptions|count}
                </span>
            {/if}
            <p class="center">{$c->__('page.groups')}</p>
            <p class="center">{$c->__('groups.subscriptions')}</p>
        </li>
    </ul>
</header>
{if="$subscriptions == null"}
    <ul class="thick">
        <div class="placeholder icon bookmark">
            <h1>{$c->__('groups.empty_title')}</h1>
            <h4>{$c->__('groups.empty_text1')} {$c->__('groups.empty_text2')}</h4>
        </li>
    </ul>
{else}
    <ul class="list divided spaced middle active all">
        {loop="$subscriptions"}
            {if="$c->checkNewServer($value)"}
                <li class="subheader" onclick="Groups_ajaxDisco('{$value->server}')">
                    <span class="control icon gray"><i class="zmdi zmdi-chevron-right"></i></span>
                    <p>
                        {$value->server} - {$value->servicename}
                    </p>
                </li>
            {/if}
            <li
                data-server="{$value->server}"
                data-node="{$value->node}"
                title="{$value->server} - {$value->node}"
            >
                {if="$value->logo"}
                    <span class="primary icon bubble">
                        <img src="{$value->getLogo()}">
                    </span>
                {else}
                    <span class="primary icon bubble color {$value->node|stringToColor}">{$value->node|firstLetterCapitalize}</span>
                {/if}
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
