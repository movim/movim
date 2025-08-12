<li id="publish_blog_presence"></li>

<li>
    <span class="primary privacy"
    title="{$c->__('publish.web_title')}">
        <form>
            <div>
                <div class="checkbox">
                    <input
                        id="public"
                        name="public"
                        onchange="Publish_ajaxTogglePrivacy({$draft->id}, this.checked)"
                        {if="$draft && $draft->open"}
                            checked
                        {/if}
                        type="checkbox">
                    <label for="public">
                        <i class="material-symbols"></i>
                    </label>
                </div>
            </div>
        </form>
    </span>
    <div>
        <p>{$c->__('publish.web_title')}</p>
        <p>{$c->__('publish.web_text')}</p>
        <span class="supporting line" id="publish_preview_url"></span>
    </div>
</li>

<li>
    <span class="primary comments_disabled"
    title="{$c->__('post.comments_disabled_title')}">
        <form>
            <div>
                <div class="checkbox">
                    <input
                        id="comments_disabled"
                        name="comments_disabled"
                        onchange="Publish_ajaxToggleCommentsDisabled({$draft->id}, this.checked)"
                        {if="$draft && $draft->comments_disabled"}
                            checked
                        {/if}
                        type="checkbox">
                    <label for="comments_disabled">
                        <i class="material-symbols"></i>
                    </label>
                </div>
            </div>
        </form>
    </span>
    <div>
        <p>{$c->__('post.comments_disabled_title')}</p>
        <p>{$c->__('post.comments_disabled_text')}</p>
    </div>
</li>
