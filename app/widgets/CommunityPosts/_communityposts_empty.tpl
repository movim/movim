<div class="placeholder">
    <i class="material-icons">receipt</i>
    {if="$me"}
        <h4>{$c->__('communityposts.empty_me_text')}</h4>
        <br />
        <a class="button" href="{$c->route('publish')}">
            <i class="material-icons">post_add</i>
            {$c->__('communityposts.empty_me_button')}
        </a>
    {else}
        <h4>{$c->__('post.empty')}</h4>
    {/if}
</div>
