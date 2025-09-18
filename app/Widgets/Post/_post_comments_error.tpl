{if="$c->me->hasPubsub()"}
<ul class="list middle">
    <li>
        <span class="primary icon gray">
            <i class="material-symbols">comments_disabled</i>
        </span>
        <div>
            <p class="normal">
                {$c->__('post.comments_disabled_yes')}
            </p>
        </div>
    </li>
</ul>
<hr />
<ul class="list active middle flex">
    {if="isset($post)"}
        <li  class="block" onclick="SendTo.shareArticle('{$post->getRef()}')">
            <span class="primary icon gray">
                <i class="material-symbols">share</i>
            </span>
            <div>
                <p class="normal">
                    {$c->__('button.share')}
                </p>
            </div>
        </li>
        <li  class="block" onclick="SendTo_ajaxSendContact('{$post->getRef()}')">
            <span class="primary icon gray">
                <i class="material-symbols">send</i>
            </span>
            <div>
                <p class="normal">
                    {$c->__('button.send_to')}
                </p>
            </div>
        </li>
    {else}
        <li class="block" onclick="Post.share()">
            <span class="primary icon gray">
                <i class="material-symbols">share</i>
            </span>
            <div>
                <p class="normal">
                    {$c->__('button.send_to')}
                </p>
            </div>
        </li>
    {/if}
</ul>
{/if}
