<section>
    <h3>{$c->__('post.publish_something')}</h3>

    <ul class="list active middle">
        <li onclick="MovimUtils.reload('{$c->route('publish')}'); Dialog_ajaxClear()"
            class="on_desktop"
            title="{$c->__('post.new_blog')}"
        >
            <span class="primary icon gray">
                <i class="material-symbols">post_add</i>
            </span>
            <span class="control icon gray">
                <i class="material-symbols">chevron_forward</i>
            </span>
            <div>
                <p>{$c->__('post.new_blog')}</p>
            </div>
        </li>

        {if="$c->me->hasUpload()"}
            <li onclick="PublishStories_ajaxOpen(); Dialog_ajaxClear()"
                class="on_desktop"
                title="{$c->__('stories.publish')}"
            >
                <span class="primary icon gray">
                    <i class="material-symbols">web_stories</i>
                </span>
                <span class="control icon gray">
                    <i class="material-symbols">chevron_forward</i>
                </span>
                <div>
                    <p>{$c->__('stories.publish')}</p>
                </div>
            </li>
        {/if}
    </ul>
</section>
<footer>
    <button class="button flat" onclick="Dialog_ajaxClear()">
        {$c->__('button.cancel')}
    </button>
</footer>
