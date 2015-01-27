<div>
    <span class="on_desktop icon"><i class="md md-view-list"></i></span>
    <h2>
        {$post->node}
    </h2>
</div>
<div>
    <h2 class="active" onclick="MovimTpl.hidePanel();">
        <span id="back" class="icon"><i class="md md-arrow-back"></i></span>
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
</div>
