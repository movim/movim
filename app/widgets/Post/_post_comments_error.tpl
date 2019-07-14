<ul class="list middle flex">
    <li class="block">
        <span class="primary icon gray">
            <i class="material-icons">comment</i>
        </span>
        <p class="normal">
            {$c->__('post.comments_disabled')}
        </p>
    </li>

    <li class="block">
        <p class="center">
            {if="$c->getUser()->hasPubsub()"}
                <button class="button icon flat gray" onclick="Post.share()">
                    <i class="material-icons">share</i> {$c->__('button.share')}
                </button>
            {/if}
        </p>
    </li>
</ul>
