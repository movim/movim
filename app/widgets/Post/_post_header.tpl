<div>
    {if="$post->isMicroblog()"}
        <span class="on_desktop icon"><i class="zmdi zmdi-account"></i></span>
        <h2>{$c->__('page.blog')}</h2>
    {else}
        <span class="on_desktop icon"><i class="zmdi zmdi-pages"></i></span>
        <h2>{$post->node}</h2>
    {/if}
</div>
<div>
    {if="$post->isMine()"}
        <ul class="active">
            {if="$post->isEditable()"}
                <li onclick="Publish_ajaxCreate('{$post->origin}', '{$post->node}', '{$post->nodeid}')" title="{$c->__('button.edit')}">
                    <span class="icon">
                        <i class="zmdi zmdi-edit"></i>
                    </span>
                </li>
            {/if}
            <li onclick="Post_ajaxDelete('{$post->origin}', '{$post->node}', '{$post->nodeid}')" title="{$c->__('button.delete')}">
                <span class="icon">
                    <i class="zmdi zmdi-delete"></i>
                </span>
            </li>
        </ul>
    {/if}
    <div class="return active {if="$post->isMine()"}{if="$post->isEditable()"}r2{else}r1{/if}{/if}" onclick="MovimTpl.hidePanel(); Post_ajaxClear();">
        <span id="back" class="icon"><i class="zmdi zmdi-arrow-back"></i></span>
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
