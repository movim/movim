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
                        <span class="primary icon bubble color {$post->aid|stringToColor}">
                            <i class="zmdi zmdi-account"></i>
                        </span>
                    {/if}
                {else}
                    <span class="primary icon bubble color {$post->contact->jid|stringToColor}">
                        <i class="zmdi zmdi-account"></i>
                    </span>
                {/if}
            {else}
                <span class="primary icon bubble color {$post->node|stringToColor}">
                    {$post->node|firstLetterCapitalize}
                </span>
            {/if}

            {if="!$post->isBrief()"}
                <p class="normal">
                    {$post->getTitle()|addHashtagsLinks}
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
                     – <i class="zmdi zmdi-edit"></i> {$post->updated|strtotime|prepareDate:true,true}
                {/if}
            </p>
            {if="$post->isBrief()"}
                <p class="normal">
                    {$post->getTitle()|addHashtagsLinks|addUrls|nl2br}
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
                    <i class="zmdi zmdi-eye"></i>
                </label>
                <content>
                    {if="$post->youtube"}
                        <div class="video_embed">
                            <iframe src="{$post->youtube->href}" frameborder="0" allowfullscreen></iframe>
                        </div>
                    {elseif="$post->isShort()"}
                        {loop="$post->pictures"}
                            <img class="big_picture" type="{$value->type}"
                                 src="{$value->href|protectPicture}"/>
                        {/loop}
                    {/if}
                </content>
            </section>
        {else}
            <li class="active">
                {if="$nsfw == false && $post->nsfw"}
                    <input type="checkbox" class="spoiler" id="spoiler_{$post->nodeid|cleanupId}">
                {/if}
                <section {if="!$post->isShort()"}class="limited"{/if} dir="{if="$post->isRTL()"}rtl{else}ltr{/if}">
                    <label class="spoiler" for="spoiler_{$post->nodeid|cleanupId}">
                        <i class="zmdi zmdi-eye"></i>
                    </label>
                    <content>
                        {if="$post->youtube"}
                            <div class="video_embed">
                                <iframe src="{$post->youtube->href}" frameborder="0" allowfullscreen></iframe>
                            </div>
                        {elseif="$post->isShort()"}
                            {loop="$post->pictures"}
                                <img class="big_picture" type="{$value->type}"
                                     src="{$value->href|protectPicture}"/>
                            {/loop}
                        {/if}
                        {$post->getContent()|addHashtagsLinks}
                    </content>
                <section>
            </li>
        {/if}

        {$c->preparePostReply($post)}

        {$c->preparePostLinks($post)}

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
                    <i class="zmdi zmdi-plus"></i> {$c->__('post.more')}
                </a>
                {if="$post->hasCommentsNode()"}
                    {$liked = $post->isLiked()}

                    {if="$liked"}
                        <a class="button icon flat red" href="{$c->route('post', [$post->server, $post->node, $post->nodeid])}">
                            {$post->countLikes()} <i class="zmdi zmdi-favorite"></i>
                        </a>
                    {else}
                        <a class="button icon flat gray" href="#"
                           onclick="this.classList.add('disabled'); PostActions_ajaxLike('{$post->server}', '{$post->node}', '{$post->nodeid}')">
                            {$post->countLikes()}
                            {if="$liked"}
                                <i class="zmdi zmdi-favorite"></i>
                            {else}
                                <i class="zmdi zmdi-favorite-outline"></i>
                            {/if}
                        </a>
                    {/if}
                    <a class="button icon flat gray" href="{$c->route('post', [$post->server, $post->node, $post->nodeid])}">
                        {$post->countComments()} <i class="zmdi zmdi-comment-outline"></i>
                    </a>
                {/if}
                {if="!$public"}
                <a
                    title="{$c->__('button.share')}"
                    class="button icon flat gray"
                    href="{$c->route('publish', [$post->server, $post->node, $post->nodeid, 'share'])}">
                    <i class="zmdi zmdi-share"></i>
                </a>
                    {if="$post->open"}
                        <a  title="{$c->__('post.public_yes')}"
                            class="button icon flat gray on_desktop"
                            target="_blank"
                            href="{$post->openlink->href}">
                            <i title="{$c->__('menu.public')}" class="zmdi zmdi-portable-wifi"></i>
                        </a>
                    {/if}
                {/if}

                {if="$post->isMine()"}
                    {if="$post->isEditable()"}
                        <a class="button icon flat oppose gray on_desktop"
                           href="{$c->route('publish', [$post->server, $post->node, $post->nodeid])}"
                           title="{$c->__('button.edit')}">
                            <i class="zmdi zmdi-edit"></i>
                        </a>
                    {/if}
                    <a class="button icon flat oppose gray on_desktop"
                       href="#"
                       onclick="PostActions_ajaxDelete('{$post->server}', '{$post->node}', '{$post->nodeid}')"
                       title="{$c->__('button.delete')}">
                        <i class="zmdi zmdi-delete"></i>
                    </a>
                {/if}
            </p>
        </li>
    </ul>
</article>
