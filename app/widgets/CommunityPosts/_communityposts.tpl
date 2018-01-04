{if="!empty($ids)"}
    <ul class="list card shadow">
    {loop="$ids"}
        {if="isset($posts[$value])"}
            <div id="{$value|cleanupId}" class="block large">
                {$c->preparePost($posts[$value])}
            </div>
        {/if}
    {/loop}
    </ul>
{elseif="$publicposts != false"}
    <ul class="list card shadow">
    {loop="$publicposts"}
        <div id="{$value->nodeid|cleanupId}" class="block large">
            {$c->preparePost($value)}
        </div>
    {/loop}
    </ul>
{else}
    <div class="placeholder icon blog">
        <h4>{$c->__('post.empty')}</h4>
    </div>
{/if}

{if="$last"}
<ul class="list active thick">
    <li onclick="CommunityPosts_ajaxGetItems('{$server}', '{$node}', '{$last}'); this.parentNode.parentNode.removeChild(this.parentNode);">
        <span class="icon primary gray">
            <i class="zmdi zmdi-time-restore"></i>
        </span>
        <p class="normal center">{$c->__('post.older')}</p>
    </li>
</ul>
{/if}
