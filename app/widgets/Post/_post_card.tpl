<article class="block large">
    <ul class="list thick">
        <li>
            {if="$post->nsfw"}
                <span class="primary icon bubble color red tiny">
                    +18
                </span>
            {elseif="$post->isMicroblog()"}
                {if="$post->contact"}
                    {$url = $post->contact->getPhoto('m')}
                    {if="$url"}
                        <span class="primary icon bubble">
                            <img src="{$url}"/>
                        </span>
                    {else}
                        <span class="primary icon bubble color {$post->contact->jid|stringToColor}">
                            <i class="material-icons">person</i>
                        </span>
                    {/if}
                {else}
                    <span class="primary icon bubble color {$post->aid|stringToColor}">
                        <i class="material-icons">person</i>
                    </span>
                {/if}
            {else}
                <span class="primary icon bubble color {$post->node|stringToColor}">
                    {$post->node|firstLetterCapitalize}
                </span>
            {/if}

            {if="!$post->isBrief()"}
                <p class="normal">
                    {autoescape="off"}
                        {$post->getTitle()|addHashtagsLinks|addEmojis}
                    {/autoescape}
                </p>
            {else}
                <p></p>
            {/if}
            <p>
                {if="$post->isMicroblog()"}
                    <a  {if="$public"}
                            href="{$c->route('blog', $post->aid)}"
                        {else}
                            href="{$c->route('contact', $post->aid)}"
                        {/if}
                    >
                        {$post->truename}
                    </a> –
                {else}
                    {if="$public"}
                        {$post->server}
                    {else}
                        <a href="{$c->route('community', $post->server)}">
                            {$post->server}
                        </a>
                    {/if} /
                    <a href="{$c->route('community', [$post->server, $post->node])}">
                        {$post->node}
                    </a> –
                {/if}
                {$post->published|strtotime|prepareDate}
                {if="$post->published != $post->updated"}
                    <i class="material-icons" title="{$post->updated|strtotime|prepareDate}">
                         edit
                    </i>
                {/if}
            </p>
            {if="$post->isBrief()"}
                <p class="normal">
                    {autoescape="off"}
                        {$post->getTitle()|addHashtagsLinks|addUrls|nl2br|prepareString}
                    {/autoescape}
                </p>
            {/if}
        </li>
    </ul>
    <ul class="list">
        {if="$post->isBrief()"}
            {if="$nsfw == false && $post->nsfw"}
                <input type="checkbox" class="spoiler" id="spoiler_{$post->nodeid|cleanupId}">
            {/if}
            <section dir="{if="$post->isRTL()"}rtl{else}ltr{/if}">
                <label class="spoiler" for="spoiler_{$post->nodeid|cleanupId}">
                    <i class="material-icons">visibility</i>
                </label>
                <content>
                    {if="$post->youtube"}
                        <div class="video_embed">
                            <iframe src="{$post->youtube->href}" frameborder="0" allowfullscreen></iframe>
                        </div>
                    {elseif="$post->isShort()"}
                        {loop="$post->pictures"}
                            <img class="big_picture" type="{$value->type}"
                                 srcset="{$value->href} 1280w, {$value->href|protectPicture} 800w"
                                 sizes="(min-width: 1280px), 800w"
                                 src="{$value->href|protectPicture}" alt="{$value->title}">
                        {/loop}
                    {/if}
                </content>
            </section>
        {else}
            <li>
                {if="$nsfw == false && $post->nsfw"}
                    <input type="checkbox" class="spoiler" id="spoiler_{$post->nodeid|cleanupId}">
                {/if}
                <section {if="!$post->isShort()"}class="limited"{/if} dir="{if="$post->isRTL()"}rtl{else}ltr{/if}">
                    <label class="spoiler" for="spoiler_{$post->nodeid|cleanupId}">
                        <i class="material-icons">visibility</i>
                    </label>
                    <content>
                        {if="$post->youtube"}
                            <div class="video_embed">
                                <iframe src="{$post->youtube->href}" frameborder="0" allowfullscreen></iframe>
                            </div>
                        {elseif="$post->isShort()"}
                            {loop="$post->pictures"}
                                <img class="big_picture" type="{$value->type}"
                                     srcset="{$value->href} 1280w, {$value->href|protectPicture} 800w"
                                     sizes="(min-width: 1280px), 800w"
                                     src="{$value->href|protectPicture}" alt="{$value->title}">
                            {/loop}
                        {/if}
                        {autoescape="off"}
                            {$post->getContent()|addHashtagsLinks}
                        {/autoescape}
                    </content>
                </section>
            </li>
        {/if}

        {autoescape="off"}
            {$c->preparePostReply($post)}
            {$c->preparePostLinks($post)}
        {/autoescape}

        <li>
            <p class="normal">
                <a class="button flat oppose"
                {if="$public"}
                    {if="$post->isMicroblog()"}
                    href="{$c->route('blog', [$post->server, $post->nodeid])}"
                    {else}
                    href="{$c->route('node', [$post->server, $post->node, $post->nodeid])}"
                    {/if}
                {else}
                    href="{$c->route('post', [$post->server, $post->node, $post->nodeid])}"
                {/if}>
                    <i class="material-icons">add</i> {$c->__('post.more')}
                </a>
                {if="$post->hasCommentsNode()"}
                    {$liked = $post->isLiked()}

                    {if="$liked"}
                        <a class="button icon flat red" href="{$c->route('post', [$post->server, $post->node, $post->nodeid])}">
                            {$post->likes->count()}
                            <i class="material-icons">favorite</i>
                        </a>
                    {else}
                        <a class="button icon flat gray" href="#"
                           onclick="this.classList.add('disabled'); PostActions_ajaxLike('{$post->server}', '{$post->node}', '{$post->nodeid}')">
                            {$post->likes->count()}
                            {if="$liked"}
                                <i class="material-icons">favorite</i>
                            {else}
                                <i class="material-icons">favorite_border</i>
                            {/if}
                        </a>
                    {/if}
                    <a class="button icon flat gray" href="{$c->route('post', [$post->server, $post->node, $post->nodeid])}">
                        {$post->comments->count()}
                        <i class="material-icons">chat_bubble_outline</i>
                    </a>
                {/if}
                {if="!$public"}
                <a
                    title="{$c->__('button.share')}"
                    class="button icon flat gray"
                    href="{$c->route('publish', [$post->server, $post->node, $post->nodeid, 'share'])}">
                    <i class="material-icons">share</i>
                </a>
                    {if="$post->openlink"}
                        <a  title="{$c->__('post.public_yes')}"
                            class="button icon flat gray on_desktop"
                            target="_blank"
                            href="{$post->openlink->href}">
                            <i title="{$c->__('menu.public')}" class="material-icons">wifi_tethering</i>
                        </a>
                    {/if}
                {/if}

                {if="$post->isMine()"}
                    {if="$post->isEditable()"}
                        <a class="button icon flat oppose gray on_desktop"
                           href="{$c->route('publish', [$post->server, $post->node, $post->nodeid])}"
                           title="{$c->__('button.edit')}">
                            <i class="material-icons">edit</i>
                        </a>
                    {/if}
                    <a class="button icon flat oppose gray on_desktop"
                       href="#"
                       onclick="PostActions_ajaxDelete('{$post->server}', '{$post->node}', '{$post->nodeid}')"
                       title="{$c->__('button.delete')}">
                        <i class="material-icons">delete</i>
                    </a>
                {/if}
            </p>
        </li>
    </ul>
</article>
