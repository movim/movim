{if="$nsfwMessage"}
    <ul class="list clear middle">
        <li>
            <span class="primary icon color bubble red">
                18+
            </span>
            <p>{$c->__('communityposts.nsfw_title')}</p>
            <p class="all">{$c->__('communityposts.nsfw_message')}</p>
        </li>
    </ul>
{/if}

{if="!empty($ids)"}
    <ul class="list card shadow">
    {loop="$ids"}
        <div id="{$value|cleanupId}" class="block large">
            {if="isset($posts[$value])"}
                {$c->preparePost($posts[$value])}
            {/if}
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
