<span id="back" class="icon" onclick="MovimTpl.hidePanel()"><i class="md md-arrow-back"></i></span>

{if="$post != null"}
    {if="$post->title != null"}
        <h2>{$post->title}</h2>
    {else}
        <h2>{$c->__('post.default_title')}</h2>
    {/if}
{else}
    <h2>Empty</h2>
{/if}
