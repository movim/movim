{if="$c->getUser()->hasPubsub()"}
<ul class="list active flex">
    {if="isset($post)"}
        <li  class="block" onclick="SendTo_ajaxSendSearch('{$post->getRef()}')">
            <span class="primary icon gray">
                <i class="material-icons">send</i>
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
                <i class="material-icons">share</i>
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
