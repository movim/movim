{if="$nsfwMessage"}
    <ul class="list clear thick">
        <li>
            <span class="primary icon color bubble red">
                18+
            </span>
            <p>Adult content blocked</p>
            <p>Some adult content has been blocked on this page. You can enable the display of adult content in the configuration page.</p>
        </li>
    </ul>
{/if}

{if="!empty($posts)"}
    <ul class="list card shadow">
        {loop="$posts"}
            <div id="{$value->nodeid|cleanupId}" class="block large">
                {$c->preparePost($value)}
            </div>
        {/loop}
    </ul>
{elseif="!empty($ids)"}
    <ul class="list card shadow">
    {loop="$ids"}
        <div id="{$value|cleanupId}" class="block large">
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
