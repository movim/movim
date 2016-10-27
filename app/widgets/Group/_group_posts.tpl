{if="$page == 0"}
    <header class="relative">
        <ul class="list middle">
            <li>
                <span id="back" class="primary icon active" onclick="MovimTpl.hidePanel(); Group_ajaxClear();">
                    <i class="zmdi zmdi-arrow-back"></i>
                </span>
            </li>
        </ul>
    </header>
{/if}

{if="!empty($posts)"}
    <ul class="list card active flex shadow">
    {loop="$posts"}
        <li class="block" onclick="MovimUtils.redirect('{$c->route('news', [$value->origin, $value->node, $value->nodeid])}')">
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

            <p class="line" {if="isset($value->title)"}title="{$value->title}"{/if}>
            {if="isset($value->title)"}
                {$value->title}
            {else}
                {$value->node}
            {/if}
            </p>
            <p dir="auto">{$value->contentcleaned|strip_tags|truncate:140}</p>
            <p>
                {$count = $value->countComments()}
                {if="$count > 0"}
                    {$count} <i class="zmdi zmdi-comment-outline"></i>
                {/if}
                <span class="info">{$value->published|strtotime|prepareDate}</span>
            </p>
        </li>
    {/loop}
    </ul>
{else}
    <div class="placeholder icon blog">
        <h4>{$c->__('post.empty')}</h4>
    </div>
{/if}

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
