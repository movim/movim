{if="$page == 0"}
    <header></header>
{/if}

<ul class="list card active flex shadow">
{loop="$posts"}
    <li class="block" onclick="MovimUtils.redirect('{$c->route('news', $value->nodeid)}')">
        {$picture = $value->getPicture()}
        {if="$picture != null"}
            <span class="icon top" style="background-image: url({$picture});"></span>
        {else}
            <span class="icon top color dark">
                {$value->node|firstLetterCapitalize}
            </span>
        {/if}

        {if="$value->logo"}
            <span class="primary icon bubble">
                <img src="{$value->getLogo()}">
            </span>
        {else}
            <span class="primary icon bubble color {$value->node|stringToColor}">{$value->node|firstLetterCapitalize}</span>
        {/if}

        <p class="line">
        {if="isset($value->title)"}
            {$value->title}
        {else}
            {$value->node}
        {/if}
        </p>
        <p>
            {$value->published|strtotime|prepareDate}
        </p>

        <p>
            {$value->contentcleaned|strip_tags}
        </p>
    </li>
{/loop}
</ul>

{if="$posts != null && count($posts) >= $paging-1"}
<ul class="list active thick">
    <li onclick="Group_ajaxGetHistory('{$server}', '{$node}', {$page+1}); this.parentNode.parentNode.removeChild(this.parentNode);">
        <span class="primary icon">
            <i class="zmdi zmdi-time-restore"></i>
        </span>
        <p class="normal line">{$c->__('post.older')}</p>
    </li>
</ul>
{/if}
