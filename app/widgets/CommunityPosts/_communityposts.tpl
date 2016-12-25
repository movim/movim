{if="!empty($posts)"}
    <ul class="list card shadow">
    {loop="$posts"}
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

{if="isset($posts) && count($posts) >= $paging-1"}
<ul class="list active thick">
    <li onclick="CommunityPosts_ajaxGetHistory('{$server}', '{$node}', {$page+1}); this.parentNode.parentNode.removeChild(this.parentNode);">
        <span class="icon primary gray">
            <i class="zmdi zmdi-time-restore"></i>
        </span>
        <p class="normal center">{$c->__('post.older')}</p>
    </li>
</ul>
{/if}
