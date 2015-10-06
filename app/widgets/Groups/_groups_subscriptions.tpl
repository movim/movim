{if="$subscriptions == null"}
    <ul class="thick">
        <div class="placeholder icon pages">
            <h1>{$c->__('groups.empty_title')}</h1>
            <h4>{$c->__('groups.empty_text1')}</h4>
            <h4>{$c->__('groups.empty_text2')}</h4>
        </li>
    </ul>
{else}
    <ul class="divided spaced middle active">
        {loop="$subscriptions"}
            {if="$c->checkNewServer($value)"}
                <li class="subheader">
                    <a href="#" onclick="Groups_ajaxDisco('{$value->server}')">{$value->server} - {$value->servicename}</a>
                </li>
            {/if}
            <li
                {if="$value->description"}class="condensed"{/if}
                data-server="{$value->server}"
                data-node="{$value->node}"
                title="{$value->server} - {$value->node}"
            >
                <span class="icon bubble color {$value->node|stringToColor}">{$value->node|firstLetterCapitalize}</span>
                <span>
                    {if="$value->name"}
                        {$value->name}
                    {else}
                        {$value->node}
                    {/if}
                </span>
                {if="$value->description"}
                    <p class="wrap">{$value->description|strip_tags}</p>
                {/if}
            </li>
        {/loop}
    </ul>
{/if}
