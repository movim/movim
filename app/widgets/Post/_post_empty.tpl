<!--<div class="placeholder icon newspaper">
    <h1>{$c->__('post.news_feed')}</h1>
    <h4>{$c->__('post.placeholder')}</h4>
</div>-->
<br/>
<h2 class="padded_top_bottom">{$c->__('post.hot')}</h2>

<ul class="flex card thick active">
{loop="$posts"}
    {if="!filter_var($value->origin, FILTER_VALIDATE_EMAIL)"}
        <li
            class="block condensed"
            data-id="{$value->nodeid}"
            data-server="{$value->origin}"
            data-node="{$value->node}">
            {if="current(explode('.', $value->origin)) == 'nsfw'"}
                <span class="icon bubble color red tiny">
                    +18
                </span>
            {else}
                <span class="icon bubble color {$value->node|stringToColor}">
                    {$value->node|firstLetterCapitalize}
                </span>
            {/if}
            <span>
            {if="isset($value->title)"}
                {$value->title}
            {else}
                {$value->node}
            {/if}
            </span>
            <p class="more">
                {if="current(explode('.', $value->origin)) != 'nsfw'"}
                    {$value->contentcleaned|strip_tags:'<img><img/>'}
                {/if}
            </p>
            <span class="info">{$value->published|strtotime|prepareDate}</span>
        </li>
        {/if}
{/loop}
</ul>
