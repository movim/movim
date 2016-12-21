{if="$external || $public"}
<article class="block">
{/if}

{if="isset($post->picture)"}
    {if="($public && $post->isPublic()) || !$public"}
        <header
            class="big"
            style="
                background-image: linear-gradient(to bottom, rgba(0,0,0,0.9) 0%, rgba(0,0,0,0.5) 100%), url('{$post->picture|echapJS}');">
    {/if}
{else}
<header class="relative">
{/if}
    {if="!$external && !$public"}
        <ul class="list middle">
            <li>
                <span class="primary icon gray active" onclick="history.back();">
                    <i class="zmdi zmdi-arrow-back"></i>
                </span>

                {if="$post->isMine() && !$public"}
                    {if="$post->isEditable()"}
                        <span class="control icon active"
                              onclick="MovimUtils.redirect('{$c->route('publish', [$post->origin, $post->node, $post->nodeid])}')"
                              title="{$c->__('button.edit')}">
                            <i class="zmdi zmdi-edit"></i>
                        </span>
                    {/if}
                    <span class="control icon active" onclick="Post_ajaxDelete('{$post->origin}', '{$post->node}', '{$post->nodeid}')" title="{$c->__('button.delete')}">
                        <i class="zmdi zmdi-delete"></i>
                    </span>
                {/if}

                <p class="line">
                    {if="$post->title != null"}
                        {$post->title}
                    {else}
                        {$c->__('post.default_title')}
                    {/if}
                </p>
            </li>
        </ul>
    {/if}

    {if="($public && $post->isPublic()) || !$public"}
    <ul class="list thick">
        <li>
            {if="$repost"}
                {$contact = $repost}
            {else}
                {$contact = $post->getContact()}
            {/if}

            {if="$post->node == 'urn:xmpp:microblog:0'"}
                {$url = $contact->getPhoto('s')}
                {if="$url"}
                    <span class="icon primary bubble">
                        <a href="#" onclick="Post_ajaxGetContact('{$contact->jid}')">
                            <img src="{$url}">
                        </a>
                    </span>
                {else}
                    <span class="icon primary bubble color {$contact->jid|stringToColor}">
                        <a href="#" onclick="Post_ajaxGetContact('{$contact->jid}')">
                            <i class="zmdi zmdi-account"></i>
                        </a>
                    </span>
                {/if}
            {else}
                {if="$post->logo"}
                    <span class="primary icon bubble">
                        <a href="{$c->route('community', [$post->origin, $post->node])}">
                            <img src="{$post->getLogo()}">
                        </a>
                    </span>
                {else}
                    <span class="primary icon bubble color {$post->node|stringToColor}">
                        <a href="{$c->route('community', [$post->origin, $post->node])}">
                            {$post->node|firstLetterCapitalize}
                        </a>
                    </span>
                {/if}
            {/if}
            <p {if="$post->title != null"}title="{$post->title|strip_tags}"{/if}>
                <a  {if="$public"}
                        {if="$post->isMicroblog()"}
                        href="{$c->route('blog', [$post->origin, $post->nodeid])}"
                        {else}
                        href="{$c->route('node', [$post->origin, $post->node, $post->nodeid])}"
                        {/if}
                    {else}
                        href="{$c->route('post', [$post->origin, $post->node, $post->nodeid])}"
                    {/if}
                    >
                    {if="$post->title != null"}
                        {$post->title}
                    {else}
                        {$c->__('post.default_title')}
                    {/if}
                </a>
            </p>
            <p>
                {if="$contact->getTrueName() != ''"}
                    {if="!$public"}
                    <a href="#" onclick="if(typeof Post_ajaxGetContact == 'function') { Post_ajaxGetContact('{$contact->jid}'); } else { Group_ajaxGetContact('{$contact->jid}'); } ">
                    {/if}
                    {$contact->getTrueName()}
                    {if="!$public"}</a>{/if} –
                {/if}
                {if="!$post->isMicroblog()"}
                    {if="!$public"}
                    <a href="{$c->route('community', $post->origin)}">
                    {/if}
                        {$post->origin}
                    {if="!$public"}</a>{/if} /
                    {if="!$public"}
                    <a href="{$c->route('community', [$post->origin, $post->node])}">
                    {/if}
                        {$post->node}
                    {if="!$public"}</a>{/if} –
                {/if}
                {$post->published|strtotime|prepareDate}
                {if="$post->published != $post->updated"}
                    - <i class="zmdi zmdi-edit"></i> {$post->updated|strtotime|prepareDate}
                {/if}
            </p>

        </li>
    </ul>
    {/if}
</header>

{if="!$external && !$public"}
<article class="block">
{/if}
    {if="$repost"}
        <a href="{$c->route('contact', $post->getContact()->jid)}">
            <ul class="list active middle">
                <li>
                    {$url = $post->getContact()->getPhoto('s')}
                    {if="$url"}
                        <span class="primary icon bubble" style="background-image: url('{$url}');">
                            <i class="zmdi zmdi-loop"></i>
                        </span>
                    {else}
                        <span class="primary icon bubble color {$post->getContact()->jid|stringToColor}">
                            <i class="zmdi zmdi-loop"></i>
                        </span>
                    {/if}

                    <span class="control icon">
                        <i class="zmdi zmdi-chevron-right"></i>
                    </span>

                    <p>{$c->__('post.repost', $post->getContact()->getTrueName())}</p>
                    <p>{$c->__('post.repost_profile', $post->getContact()->getTrueName())}</p>
                </li>
            </ul>
        </a>
    {/if}

    {if="$public && !$post->isPublic()"}
        <ul class="list thick">
            <li>
                <span class="primary icon color gray bubble">
                    <i class="zmdi zmdi-lock"></i>
                </span>
                <p class="line center normal"> {$c->__('blog.private')} - <a href="{$c->route('main')}">{$c->__('page.login')}</a></p>
            </li>
        </ul>
        <br />
    {else}
        <section dir="{if="$post->isRTL()"}rtl{else}ltr{/if}">
            {if="$post->isReply()"}
                {if="$reply"}
                    <a href="{$c->route('post', [$reply->origin, $reply->node, $reply->nodeid])}">
                        <ul class="list active thick card">
                            <li class="block">
                                {if="$reply->picture"}
                                    <span
                                        class="primary icon thumb white color"
                                        style="background-image: linear-gradient(to bottom, rgba(0, 0, 0, 0.7) 0%, rgba(0, 0, 0, 0.3) 100%), url({$reply->picture|echapJS});">
                                        <i class="zmdi zmdi-mail-reply"></i>
                                    </span>
                                {elseif="$reply->isMicroblog()"}
                                    {$url = $reply->getContact()->getPhoto('l')}
                                    {if="$url"}
                                        <span class="primary icon thumb color white" style="background-image: linear-gradient(to bottom, rgba(0, 0, 0, 0.7) 0%, rgba(0, 0, 0, 0.3) 100%), url({$url});">
                                            <i class="zmdi zmdi-mail-reply"></i>
                                        </span>
                                    {else}
                                        <span class="primary icon thumb color {$value->getContact()->jid|stringToColor}">
                                            <i class="zmdi zmdi-mail-reply"></i>
                                        </span>
                                    {/if}
                                {/if}
                                <span class="control icon gray">
                                    <i class="zmdi zmdi-chevron-right"></i>
                                </span>
                                <p class="line">{$reply->title}</p>
                                <p>{$reply->getSummary()}</p>
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
                    </a>
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
        </section>
        <footer>
            {$tags = $post->getTags()}
            {if="isset($tags)"}
                <ul class="list thick">
                    <li>
                        <span class="primary icon zmdi zmdi-tag gray"></span>
                        <p></p>
                        <p class="normal">
                            {loop="$tags"}
                                <a target="_blank" href="{$c->route('tag', [$value])}">#{$value}</a>
                            {/loop}
                        </p>
                    </li>
                </ul>
            {/if}
            <ul class="list middle divided spaced">
                {if="isset($attachments.links)"}
                    {loop="$attachments.links"}
                        {if="$value.rel != 'alternate' && $post->picture != $value['href'] && $post->open != $value['href']"}
                            <li>
                                <span class="primary icon">
                                    <img src="https://icons.duckduckgo.com/ip2/{$value.url.host}.ico"/>
                                </span>
                                <p class="normal line">
                                    <a title="{$value.href|urldecode}" href="{$value.href}" class="alternate" target="_blank">
                                        {$value.href|urldecode}
                                    </a>
                                </p>
                            </li>
                        {/if}
                    {/loop}
                {/if}
                {if="isset($attachments.files)"}
                    {loop="$attachments.files"}
                        <li>
                            <span class="primary icon gray">
                                <span class="zmdi zmdi-attachment-alt"></span>
                            </span>
                            <p class="normal line">
                                <a
                                    href="{$value.href}"
                                    class="enclosure"
                                    {if="isset($value.type)"}
                                        type="{$value.type}"
                                    {/if}
                                    target="_blank">
                                {$value.href|urldecode}
                                </a>
                            </p>
                        </li>
                    {/loop}
                {/if}
            </ul>
            {if="!$post->isShort() && isset($attachments.pictures)"}
                <ul class="list flex middle">
                {loop="$attachments.pictures"}
                    {if="$value.type != 'picture'"}
                    <li class="block pic">
                        <span class="primary icon gray">
                            <i class="zmdi zmdi-image"></i>
                        </span>
                        <a href="{$value.href}" class="alternate" target="_blank">
                            <img type="{$value.type}" src="{$value.href|urldecode}"/>
                        </a>
                    </li>
                    {/if}
                {/loop}
                </ul>
            {/if}
            {if="$post->isPublic() && !$public"}
                <ul class="list active middle thick">
                    <li>
                        <span class="primary icon gray">
                            <i class="zmdi zmdi-portable-wifi"></i>
                        </span>
                        <p class="line normal">
                            {$c->__('post.public_yes')}
                        </p>
                        <p>
                            <a title="{$post->getPublicUrl()}" target="_blank" href="{$post->getPublicUrl()}">
                                {$c->__('post.public_url')}
                            </a>
                        </p>
                    </li>
                </ul>
            {/if}
        </footer>

        {if="$external"}
            {$comments = $c->getComments($post)}
            {if="$comments"}
                <ul class="list spaced middle">
                    <li class="subheader">
                        <p>
                            <span class="info">{$comments|count}</span>
                            {$c->__('post.comments')}
                        </p>
                    </li>
                    {loop="$comments"}
                        {if="$value->title || $value->contentraw"}
                        <li>
                            {$url = $value->getContact()->getPhoto('s')}
                            {if="$url"}
                                <span class="primary icon bubble">
                                    <img src="{$url}">
                                </span>
                            {else}
                                <span class="primary icon bubble color {$value->getContact()->jid|stringToColor}">
                                    <i class="zmdi zmdi-account"></i>
                                </span>
                            {/if}
                            <p>
                                {$value->getContact()->getTrueName()}
                            </p>
                            <p class="all">
                                <span class="info">{$value->published|strtotime|prepareDate}</span>
                                {if="$value->title"}
                                    {$value->title}
                                {else}
                                    {$value->contentraw}
                                {/if}
                            </p>
                        </li>
                        {/if}
                    {/loop}
                </ul><br />
            {/if}
        {else}
            <div id="comments" class="spin"></div>
        {/if}
    {/if}

    {if="!$external"}
            {$next = $post->getNext()}
            {$previous = $post->getPrevious()}
            {if="$next || $previous"}
                <ul class="list card flex active">
                    {if="$previous"}
                        <li class="block"
                            onclick="MovimUtils.redirect('{$c->route('post', [$previous->origin, $previous->node, $previous->nodeid])}')">
                            <span class="primary icon gray">
                                <i class="zmdi zmdi-arrow-left"></i>
                            </span>
                            <p class="line" {if="isset($previous->title)"}title="{$previous->title}"{/if}>
                            {if="isset($previous->title)"}
                                {$previous->title}
                            {else}
                                {$previous->node}
                            {/if}
                            </p>
                            <p class="line">{$previous->getSummary()}</p>
                            <p>
                                {$count = $previous->countComments()}
                                {if="$count > 0"}
                                    {$count} <i class="zmdi zmdi-comment-outline"></i>
                                {/if}
                                <span class="info">
                                    {$previous->published|strtotime|prepareDate}
                                </span>
                            </p>
                        </li>
                    {/if}
                    {if="$next"}
                        <li class="block"
                            onclick="MovimUtils.redirect('{$c->route('post', [$next->origin, $next->node, $next->nodeid])}')">
                            <span class="control icon gray">
                                <i class="zmdi zmdi-arrow-right"></i>
                            </span>
                            <p class="line" {if="isset($next->title)"}title="{$next->title}"{/if}>
                            {if="isset($next->title)"}
                                {$next->title}
                            {else}
                                {$next->node}
                            {/if}
                            </p>
                            <p class="line">{$next->getSummary()}</p>
                            <p>
                                {$count = $next->countComments()}
                                {if="$count > 0"}
                                    {$count} <i class="zmdi zmdi-comment-outline"></i>
                                {/if}
                                <span class="info">
                                    {$next->published|strtotime|prepareDate}
                                </span>
                            </p>
                        </li>
                    {/if}
                </ul>
            {/if}
        {/if}
</article>
