{if="$subscriptions == null"}
    <ul class="thick">
        <div class="placeholder icon bookmark">
            <h1>{$c->__('groups.empty_title')}</h1>
            <h4>{$c->__('groups.empty_text1')}</h4>
            <h4>{$c->__('groups.empty_text2')}</h4>
        </li>
    </ul>
{else}
    <ul class="list divided spaced middle active">
        {loop="$subscriptions"}
            {if="$c->checkNewServer($value)"}
                <li class="subheader">
                    <p>
                        <a href="#" onclick="Groups_ajaxDisco('{$value->server}')">{$value->server} - {$value->servicename}</a>
                    </p>
                </li>
            {/if}
            <li
                data-server="{$value->server}"
                data-node="{$value->node}"
                title="{$value->server} - {$value->node}"
            >
                <span class="primary icon bubble color {$value->node|stringToColor}">{$value->node|firstLetterCapitalize}</span>
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
