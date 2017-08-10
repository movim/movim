<article class="block large">
    <ul class="list thick">
        <li>
            {if="$post->isNSFW()"}
                <span class="primary icon bubble color red tiny">
                    +18
                </span>
            {elseif="$post->logo"}
                <span class="primary icon bubble color white">
                    <img src="{$post->getLogo()}"/>
                </span>
            {elseif="$post->isMicroblog()"}
                {$url = $post->getContact()->getPhoto('m')}
                {if="$url"}
                    <span class="primary icon bubble color white">
                        <img src="{$url}"/>
                    </span>
                {else}
                    <span class="primary icon bubble color {$post->getContact()->jid|stringToColor}">
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
                            href="{$c->route('blog', $post->getContact()->jid)}"
                        {else}
                            href="{$c->route('contact', $post->getContact()->jid)}"
                        {/if}
                    >
                        {$post->getContact()->getTrueName()}
                    </a> –
                {else}
                    {if="$public"}
                        {$post->origin}
                    {else}
                        <a href="{$c->route('community', $post->origin)}">
                            {$post->origin}
                        </a>
                    {/if} /
                    <a href="{$c->route('community', [$post->origin, $post->node])}">
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
        {if="!$post->isBrief()"}
        <li class="active">
            <p>
                <section {if="!$post->isShort()"}class="limited"{/if}>
                    <content>
                        {if="$post->getYoutube()"}
                            <div class="video_embed">
                                <iframe src="https://www.youtube.com/embed/{$post->getYoutube()}" frameborder="0" allowfullscreen></iframe>
                            </div>
                        {elseif="$post->isShort() && isset($attachments.pictures)"}
                            {loop="$attachments.pictures"}
                                {if="$value.type != 'picture'"}
                                <a href="{$value.href}" class="alternate" target="_blank">
                                    <img class="big_picture" type="{$value.type}" src="{$value.href|urldecode}"/>
                                </a>
                                {/if}
                            {/loop}
                        {/if}
                        {$post->getContent()|addHashtagsLinks}
                    </content>
                <section>
            </p>
        </li>
        {else}
            <section>
                <content>
                    {if="$post->getYoutube()"}
                        <div class="video_embed">
                            <iframe src="https://www.youtube.com/embed/{$post->getYoutube()}" frameborder="0" allowfullscreen></iframe>
                        </div>
                    {elseif="$post->isShort() && isset($attachments.pictures)"}
                        {loop="$attachments.pictures"}
                            {if="$value.type != 'picture'"}
                            <a href="{$value.href}" class="alternate" target="_blank">
                                <img class="big_picture" type="{$value.type}" src="{$value.href|urldecode}"/>
                            </a>
                            {/if}
                        {/loop}
                    {/if}
                </content>
            </section>
        {/if}

        {if="$post->isReply()"}
            <hr />
            {$reply = $post->getReply()}
            {if="$reply"}
                <ul class="list thick active faded">
                    <li>
                        {if="$reply->picture"}
                            <span
                                class="primary icon bubble color white"
                                style="background-image: linear-gradient(to bottom, rgba(0, 0, 0, 0.7) 0%, rgba(0, 0, 0, 0.3) 100%), url({$reply->picture});">
                                <i class="zmdi zmdi-mail-reply"></i>
                            </span>
                        {elseif="$reply->isMicroblog()"}
                            {$url = $reply->getContact()->getPhoto('l')}
                            {if="$url"}
                                <span class="primary icon bubble color white" style="background-image: linear-gradient(to bottom, rgba(0, 0, 0, 0.7) 0%, rgba(0, 0, 0, 0.3) 100%), url({$url});">
                                    <i class="zmdi zmdi-mail-reply"></i>
                                </span>
                            {else}
                                <span class="primary icon bubble color {$reply->getContact()->jid|stringToColor}">
                                    <i class="zmdi zmdi-mail-reply"></i>
                                </span>
                            {/if}
                        {/if}
                        <p class="normal line">{$reply->title}</p>
                        <p>{$reply->getContent()|html_entity_decode|stripTags}</p>
                        <p>
                            {if="$reply->isMicroblog()"}
                                <i class="zmdi zmdi-account"></i> {$reply->getContact()->getTrueName()}
                            {else}
                                <i class="zmdi zmdi-pages"></i> {$reply->node}
                            {/if}
                            <span class="info">
                                {$reply->published|strtotime|prepareDate:true,true}
                            </span>
                        </p>
                    </li>
                </ul>
            {else}
                <ul class="list thick active faded">
                    <li>
                        <span class="primary icon gray">
                            <i class="zmdi zmdi-mail-reply"></i>
                        </span>
                        <p class="line normal">{$c->__('post.original_deleted')}</p>
                    </li>
                </ul>
            {/if}
        {/if}

        {if="isset($attachments.links)"}
            {loop="$attachments.links"}
                {if="!empty($value.title)"}
                <ul class="list">
                    <li>
                        <span class="primary icon gray">
                            {if="isset($value.logo)"}
                                <img src="{$value.logo}"/>
                            {else}
                                <i class="zmdi zmdi-link"></i>
                            {/if}
                        </span>
                        <p class="normal line">
                            <a target="_blank" href="{$value.href}" title="{$value.href}">
                                {if="$value.title"}
                                    {$value.title}
                                {else}
                                    {$value.href}
                                {/if}
                            </a>
                        </p>
                        {if="isset($value.description) && !empty($value.description)"}
                            <p title="{$value.description}">{$value.description}</p>
                        {else}
                            <p>{$value.url.host}</p>
                        {/if}
                    </li>
                </ul>
                {/if}
            {/loop}
        {/if}

        <li>
            <p class="normal">
                <a class="button flat oppose"
                {if="$public"}
                    {if="$post->isMicroblog()"}
                    href="{$c->route('blog', [$post->origin, $post->nodeid])}"
                    {else}
                    href="{$c->route('node', [$post->origin, $post->node, $post->nodeid])}"
                    {/if}
                {else}
                    href="{$c->route('post', [$post->origin, $post->node, $post->nodeid])}"
                {/if}>
                    <i class="zmdi zmdi-plus"></i> {$c->__('post.more')}
                </a>
                {if="$post->hasCommentsNode()"}
                <a class="button icon flat gray" href="{$c->route('post', [$post->origin, $post->node, $post->nodeid])}">
                    {$post->countLikes()} <i class="zmdi zmdi-favorite-outline"></i>
                </a><a class="button icon flat gray" href="{$c->route('post', [$post->origin, $post->node, $post->nodeid])}">
                    {$post->countComments()} <i class="zmdi zmdi-comment-outline"></i>
                </a>
                {/if}
                {if="!$public"}
                <a class="button icon flat gray" href="{$c->route('publish', [$post->origin, $post->node, $post->nodeid, 'share'])}">
                    <i class="zmdi zmdi-mail-reply"></i>
                </a>
                    {if="$post->isPublic()"}
                        <a class="button icon flat gray on_desktop" target="_blank" href="{$post->getPublicUrl()}">
                            <i title="{$c->__('menu.public')}" class="zmdi zmdi-portable-wifi"></i>
                        </a>
                    {/if}
                {/if}

                {if="$post->isMine()"}
                    {if="$post->isEditable()"}
                        <a class="button icon flat oppose gray on_desktop"
                           href="{$c->route('publish', [$post->origin, $post->node, $post->nodeid])}"
                           title="{$c->__('button.edit')}">
                            <i class="zmdi zmdi-edit"></i>
                        </a>
                    {/if}
                    <a class="button icon flat oppose gray on_desktop"
                       href="#"
                       onclick="PostActions_ajaxDelete('{$post->origin}', '{$post->node}', '{$post->nodeid}')"
                       title="{$c->__('button.delete')}">
                        <i class="zmdi zmdi-delete"></i>
                    </a>
                {/if}
            </p>
        </li>
    </ul>
</article>
