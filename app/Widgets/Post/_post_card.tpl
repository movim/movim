<article class="block large">
    <ul class="list thick">
        <li>
            {if="$post->nsfw"}
                <span class="primary icon bubble color red tiny">
                    +18
                </span>
            {elseif="$post->isMicroblog()"}
                {if="$post->contact"}
                    <span class="primary icon bubble">
                        <img src="{$post->contact->getPicture(\Movim\ImageSize::M)}"/>
                    </span>
                {else}
                    <span class="primary icon bubble color {$post->aid|stringToColor}">
                        <i class="material-symbols">person</i>
                    </span>
                {/if}
            {else}
                {if="$post->info != null"}
                    <span class="primary icon bubble">
                        <img src="{$post->info->getPicture(\Movim\ImageSize::L)}"/>
                    </span>
                {else}
                    <span class="primary icon bubble color {$post->node|stringToColor}">
                        {$post->node|firstLetterCapitalize}
                    </span>
                {/if}
            {/if}

            <div>
                {if="!$post->isBrief()"}
                    <p class="line two" title="{$post->title}">
                        {autoescape="off"}
                            {$post->title}
                        {/autoescape}
                    </p>
                {else}
                    <p></p>
                {/if}

                <p title="{$post->published|prepareDate}">
                    {if="$post->aid"}
                        {if="!$post->isMicroblog() && $post->contact"}
                            <span class="icon bubble tiny">
                                <img src="{$post->contact->getPicture()}">
                            </span>
                        {/if}
                        <a  {if="$public"}
                                href="{$c->route('blog', $post->aid)}"
                            {else}
                                href="#" onclick="MovimUtils.reload('{$c->route('contact', $post->aid)}')"
                            {/if}
                        >
                            {$post->truename}
                        </a> •
                    {/if}

                    {if="!$post->isMicroblog()"}
                        {if="$public"}
                            {$post->server}
                        {else}
                            <a href="#" onclick="MovimUtils.reload('{$c->route('community', $post->server)}')">
                                {$post->server}
                            </a>
                        {/if} /
                        <a href="#" onclick="MovimUtils.reload('{$c->route('community', [$post->server, $post->node])}')">
                            {$post->node}
                        </a> •
                    {/if}
                    {$post->published|prepareDate:true,true}
                    {if="$post->isEdited()"}
                        <i class="material-symbols" title="{$post->updated|prepareDate}">
                            edit
                        </i>
                    {/if}

                    {if="!$post->openlink"}
                        <i class="material-symbols on_mobile" title="{$c->__('post.public_no')}">
                            lock
                        </i>
                    {/if}
                    {if="$post->contentcleaned && readTime($post->contentcleaned)"}
                        • {$post->contentcleaned|readTime}
                    {/if}
                    {$count = $post->user_views_count}
                    {if="$count > 2"}
                        • {$count} <i class="material-symbols">visibility</i>
                    {/if}
                </p>
                {if="$post->isBrief()"}
                    <p class="normal brief" title="{$post->title}">
                        {autoescape="off"}
                            {$post->title|addUrls|addHashtagsLinks|nl2br|prepareString|addEmojis}
                        {/autoescape}
                    </p>
                {/if}
            </div>
        </li>
    </ul>
    {if="$post->isBrief()"}
        {if="$nsfw == false && $post->nsfw"}
            <input type="checkbox" class="spoiler" id="spoiler_{$post->nodeid|cleanupId}">
        {/if}
        <section dir="{if="$post->isRTL()"}rtl{else}ltr{/if}">
            <label class="spoiler" for="spoiler_{$post->nodeid|cleanupId}">
                <i class="material-symbols">visibility</i>
            </label>
            <div>
                {if="$post->embeds->count() > 0"}
                    {loop="$post->embeds"}
                        <div class="video_embed shimmer">
                            <iframe class="spin" src="{$value->href}" frameborder="0" allowfullscreen></iframe>
                        </div>
                    {/loop}
                {else}
                    {loop="$post->pictures"}
                        <img class="big_picture"
                                type="{$value->type}"
                                src="{$value->href|protectPicture}"
                                {if="!empty($value->title)"}
                                title="{$value->title}"
                                alt="{$value->title}"
                                {/if}
                        >
                    {/loop}
                {/if}
            </div>
        </section>
    {else}
        {if="$nsfw == false && $post->nsfw"}
            <input type="checkbox" class="spoiler" id="spoiler_{$post->nodeid|cleanupId}">
        {/if}
        <section {if="!$post->isShort()"}class="limited"{/if} dir="{if="$post->isRTL()"}rtl{else}ltr{/if}">
            <label class="spoiler" for="spoiler_{$post->nodeid|cleanupId}">
                <i class="material-symbols">visibility</i>
            </label>
            <div>
                {if="$post->embeds->count() > 0"}
                    {loop="$post->embeds"}
                        <div class="video_embed shimmer">
                            <iframe class="spin" src="{$value->href}" frameborder="0" allowfullscreen></iframe>
                        </div>
                    {/loop}
                {elseif="$post->isShort()"}
                    {loop="$post->pictures"}
                        <img class="big_picture"
                                type="{$value->type}"
                                src="{$value->href|protectPicture}"
                                {if="!empty($value->title)"}
                                title="{$value->title}"
                                alt="{$value->title}"
                                {/if}
                        >
                    {/loop}
                {/if}
                {autoescape="off"}
                    {$post->getContent(true)}
                {/autoescape}
            </div>
        </section>
    {/if}

    {autoescape="off"}
        {$c->preparePostReply($post)}
        {$c->preparePostLinks($post)}
    {/autoescape}

    <ul class="list">
        <li>
            <div>
                <p class="normal">
                    <a class="button flat oppose"
                    {if="$public"}
                        {if="$post->isMicroblog()"}
                            onclick="MovimUtils.reload('{$c->route('blog', [$post->server, $post->nodeid])}')"
                        {else}
                            onclick="MovimUtils.reload('{$c->route('community', [$post->server, $post->node, $post->nodeid])}')"
                        {/if}
                    {else}
                        onclick="MovimUtils.reload('{$c->route('post', [$post->server, $post->node, $post->nodeid])}')"
                    {/if}>
                        <i class="material-symbols on_desktop">add</i> {$c->__('post.more')}
                    </a>
                    {if="$post->hasCommentsNode()"}
                        {$liked = $post->isLiked()}

                        {if="$liked"}
                            <a class="button narrow icon flat red" onclick="MovimUtils.reload('{$c->route('post', [$post->server, $post->node, $post->nodeid])}')" href="#">
                                {if="$post->likes->count() > 0"}{$post->likes->count()}{/if}
                                <i class="material-symbols fill">favorite</i>
                            </a>
                        {else}
                            <a class="button narrow icon flat gray" href="#"
                            onclick="this.classList.add('disabled'); PostActions_ajaxLike('{$post->server}', '{$post->node}', '{$post->nodeid}')">
                            {if="$post->likes->count() > 0"}{$post->likes->count()}{/if}
                                {if="$liked"}
                                    <i class="material-symbols fill">favorite</i>
                                {else}
                                    <i class="material-symbols">favorite</i>
                                {/if}
                            </a>
                        {/if}
                        <a class="button narrow icon flat gray" onclick="MovimUtils.reload('{$c->route('post', [$post->server, $post->node, $post->nodeid], [], 'comment')}')" href="#">
                            {if="$post->comments->count() > 0"}{$post->comments->count()}{/if}
                            <i class="material-symbols">chat_bubble_outline</i>
                        </a>
                    {/if}
                    {if="!$public"}
                        <a
                            title="{$c->__('button.share')}"
                            class="button narrow icon flat gray"
                            onclick="SendTo.shareArticle('{$post->getRef()}')"
                            href="#">
                            <i class="material-symbols">share</i>
                        </a>
                        <a
                            title="{$c->__('button.send_to')}"
                            class="button narrow icon flat gray"
                            onclick="SendTo_ajaxSendContact('{$post->getRef()}')"
                            href="#">
                            <i class="material-symbols">send</i>
                        </a>
                        {if="$post->openlink"}
                            <a  title="{$c->__('post.public_url')}"
                                class="button narrow icon flat gray oppose"
                                target="_blank"
                                href="{$post->openlink->href}">
                                <i class="material-symbols">open_in_new</i>
                            </a>
                        {else}
                            <a  class="button narrow icon flat gray on_desktop oppose"
                                title="{$c->__('post.public_no')}">
                                <i class="material-symbols">lock</i>
                            </a>
                        {/if}
                    {/if}

                    {if="$post->isMine($c->me) || ($post->userAffiliation && $post->userAffiliation->affiliation == 'owner')"}
                        {if="$post->isEditable()"}
                            <a class="button narrow icon flat oppose gray on_desktop"
                            href="{$c->route('publish', [$post->server, $post->node, $post->nodeid])}"
                            title="{$c->__('button.edit')}">
                                <i class="material-symbols">edit</i>
                            </a>
                        {/if}
                        <a class="button narrow icon flat oppose gray on_desktop"
                        href="#"
                        onclick="PostActions_ajaxDelete('{$post->server}', '{$post->node}', '{$post->nodeid}')"
                        title="{$c->__('button.delete')}">
                            <i class="material-symbols">delete</i>
                        </a>
                    {/if}
                </p>
            </div>
        </li>
    </ul>
</article>
