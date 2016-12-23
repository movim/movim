<ul class="list middle flex">
    <li class="block">
        <span class="primary icon gray">
            <i class="zmdi zmdi-comment"></i>
        </span>
        <p class="normal">
            {$c->__('post.comments_disabled')}
        </p>
    </li>

    <li class="block">
        <p class="center">
            {if="$c->supported('pubsub')"}
            <a class="button flat gray" onclick="Post.share()">
                <i class="zmdi zmdi-share"></i> {$c->__('button.share')}
            </a>
            {/if}
        </p>
    </li>
</ul>
