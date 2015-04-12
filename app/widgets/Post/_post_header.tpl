<div>
    <span class="on_desktop icon"><i class="md md-view-list"></i></span>
    <h2>
        {if="$post->node == 'urn:xmpp:microblog:0'"}
            {$c->__('page.blog')}
        {else}
            {$post->node}
        {/if}
    </h2>
</div>
<div>
    {if="$post->isMine()"}
        <ul class="active">
            <li onclick="Post_ajaxDelete('{$post->origin}', '{$post->node}', '{$post->nodeid}')">
                <span class="icon">
                    <i class="md md-delete"></i>
                </span>
            </li>
        </ul>
    {/if}
    <div class="return active {if="$post->isMine()"}r1{/if}" onclick="MovimTpl.hidePanel(); Post_ajaxClear();">
        <span id="back" class="icon"><i class="md md-arrow-back"></i></span>
        <h2>
            {if="$post != null"}
                {if="$post->title != null"}
                    {$post->title}
                {else}
                    {$c->__('post.default_title')}
                {/if}
            {else}
                Empty
            {/if}
        </h2>
    </h2>
</div>
