<article class="block large">
    <ul class="list thick">
        <li>
            {if="$post->isNSFW()"}
                <span class="primary icon bubble color red tiny">
                    +18
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

            <p class="normal">{$post->title}</p>
            <p>
                {if="$post->isMicroblog()"}
                    <a href="{$c->route('contact', $post->getContact()->jid)}">
                        {$post->getContact()->getTrueName()}
                    </a> –
                {else}
                    <a href="{$c->route('community', $post->origin)}">
                        {$post->origin}
                    </a> /
                    <a href="{$c->route('community', [$post->origin, $post->node])}">
                        {$post->node}
                    </a> –
                {/if}
                {$post->published|strtotime|prepareDate}
                {if="$post->published != $post->updated"}
                     – <i class="zmdi zmdi-edit"></i> {$post->updated|strtotime|prepareDate:true,true}
                {/if}
            </p>
        </li>
    </ul>
    <ul class="list">
        <li class="active">
            <p>
            </p>
            <p>
                <section {if="!$post->isShort()"}class="limited"{/if}>
                    {if="$post->isReply()"}
                        {$reply = $post->getReply()}
                        {if="$reply"}
                            <ul class="list thick card">
                                <li class="block">
                                    {if="$reply->picture"}
                                        <span
                                            class="primary icon thumb color white"
                                            style="background-image: linear-gradient(to bottom, rgba(0, 0, 0, 0.7) 0%, rgba(0, 0, 0, 0.3) 100%), url({$reply->picture});">
                                            <i class="zmdi zmdi-mail-reply"></i>
                                        </span>
                                    {/if}
                                    <p class="line">{$reply->title}</p>
                                    <p>{$reply->contentcleaned|html_entity_decode|stripTags}</p>
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
                            <ul class="list thick card">
                                <li class="block">
                                    <span class="primary icon gray">
                                        <i class="zmdi zmdi-info-outline"></i>
                                    </span>
                                    <p class="line normal">{$c->__('post.original_deleted')}</p>
                                </li>
                            </ul>
                        {/if}
                    {/if}
                    <content>
                        {if="$post->isShort() && isset($attachments.pictures)"}
                            {loop="$attachments.pictures"}
                                {if="$value.type != 'picture'"}
                                <a href="{$value.href}" class="alternate" target="_blank">
                                    <img class="big_picture" type="{$value.type}" src="{$value.href|urldecode}"/>
                                </a>
                                {/if}
                            {/loop}
                        {/if}
                        {if="$post->getYoutube()"}
                            <div class="video_embed">
                                <iframe src="https://www.youtube.com/embed/{$post->getYoutube()}" frameborder="0" allowfullscreen></iframe>
                            </div>
                        {/if}
                        {$post->contentcleaned|html_entity_decode}
                    </content>
                <section>
            </p>
        </li>

        <li>
            <p class="normal center">
                <a class="button flat" href="{$c->route('post', [$post->origin, $post->node, $post->nodeid])}">
                    <i class="zmdi zmdi-plus"></i> {$c->__('post.more')}
                </a>
            </p>
            <p class="normal">
                <a class="button flat gray" href="{$c->route('post', [$post->origin, $post->node, $post->nodeid])}">
                    {$post->countLikes()} <i class="zmdi zmdi-favorite-outline"></i>
                </a>
                <a class="button flat gray" href="{$c->route('post', [$post->origin, $post->node, $post->nodeid])}">
                    {$post->countComments()} <i class="zmdi zmdi-comment-outline"></i>
                </a>
                <a class="button flat gray" href="{$c->route('publish', [$post->origin, $post->node, $post->nodeid, 'share'])}">
                    <i class="zmdi zmdi-share"></i>
                </a>
                {if="$post->isPublic()"}
                    <a class="button flat gray" target="_blank" href="{$post->getPublicUrl()}">
                        <i title="{$c->__('menu.public')}" class="zmdi zmdi-portable-wifi"></i>
                    </a>
                {/if}
            </p>
        </li>
    </ul>
</article>
