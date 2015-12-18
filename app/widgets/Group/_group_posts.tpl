{loop="$posts"}
    {$c->preparePost($value)}
{/loop}
{if="$posts != null && count($posts) >= $paging-1"}
<ul class="active thick">
    <li onclick="Group_ajaxGetHistory('{$server}', '{$node}', {$page+1}); this.parentNode.parentNode.removeChild(this.parentNode);">
        <span class="icon">
            <i class="zmdi zmdi-time-restore"></i>
        </span>
        {$c->__('post.older')}
    </li>
</ul>
{/if}
