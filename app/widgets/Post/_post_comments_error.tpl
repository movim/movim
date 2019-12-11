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
                {if="isset($post)"}
                    <button class="button icon flat gray" onclick="SendTo_ajaxSendSearch('{$post->getRef()}')">
                        <i class="material-icons">send</i> {$c->__('button.share')}
                    </button>
                {else}
                    <button class="button icon flat gray" onclick="Post.share()">
                        <i class="material-icons">share</i> {$c->__('button.share')}
                    </button>
                {/if}
            {/if}
        </p>
    </li>
</ul>
