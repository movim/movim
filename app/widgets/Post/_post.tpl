<article class="block">
<header class="relative">
    {if="!$external && !$public"}
        <ul class="list middle">
            <li>
                <span class="primary icon gray active" onclick="history.back();">
                    <i class="zmdi zmdi-arrow-back"></i>
                </span>

                {if="$post->isMine()"}
                    {if="$post->isEditable()"}
                        <span class="control icon active gray"
                              onclick="MovimUtils.redirect('{$c->route('publish', [$post->server, $post->node, $post->nodeid])}')"
                              title="{$c->__('button.edit')}">
                            <i class="zmdi zmdi-edit"></i>
                        </span>
                    {/if}
                    <span class="control icon active gray"
                          onclick="PostActions_ajaxDelete('{$post->server}', '{$post->node}', '{$post->nodeid}')"
                          title="{$c->__('button.delete')}">
                        <i class="zmdi zmdi-delete"></i>
                    </span>
                {/if}

                <p class="line">
                    {if="$post->title != null && !$post->isBrief()"}
                        {$post->getTitle()}
                    {else}
                        {$c->__('post.default_title')}
                    {/if}
                </p>
            </li>
        </ul>
    {/if}

    {if="($public && $post->open) || !$public"}
    <ul class="list thick">
        <li>
            {if="$repost"}
                {$contact = $repost}
            {else}
                {$contact = $post->contact}
            {/if}

            {if="$post->isMicroblog()"}
                {$url = $contact->getPhoto('s')}
                {if="$post->nsfw"}
                    <span class="primary icon bubble color red tiny">
                        +18
                    </span>
                {elseif="$url"}
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
                {if="$post->nsfw"}
                    <span class="primary icon bubble color red tiny">
                        +18
                    </span>
                {elseif="$post->logo"}
                    <span class="primary icon bubble">
                        <a href="{$c->route('community', [$post->server, $post->node])}">
                            <img src="{$post->getLogo()}">
                        </a>
                    </span>
                {else}
                    <span class="primary icon bubble color {$post->node|stringToColor}">
                        <a href="{$c->route('community', [$post->server, $post->node])}">
                            {$post->node|firstLetterCapitalize}
                        </a>
                    </span>
                {/if}
            {/if}
            {if="$public"}
            <span class="control icon active">
                <a  {if="$public"}
                    {if="$post->isMicroblog()"}
                    href="{$c->route('blog', [$post->server, $post->nodeid])}"
                    {else}
                    href="{$c->route('node', [$post->server, $post->node, $post->nodeid])}"
                    {/if}
                {else}
                    href="{$c->route('post', [$post->server, $post->node, $post->nodeid])}"
                {/if}
                >
                    <i class="zmdi zmdi-chevron-right"></i>
                </a>
            </span>
            {/if}
            {if="!$post->isBrief()"}
                <p {if="$post->title != null"}title="{$post->title|strip_tags}"{/if}>
                    {$post->getTitle()|addHashtagsLinks}
                </p>
            {else}
                <p></p>
            {/if}
            <p>
                {if="$contact && $contact->truename != ''"}
                    {if="!$public"}
                    <a href="#" onclick="if (typeof Post_ajaxGetContact == 'function') { Post_ajaxGetContact('{$contact->jid}'); } else { Group_ajaxGetContact('{$contact->jid}'); } ">
                    {/if}
                    {$contact->truename}
                    {if="!$public"}</a>{/if} –
                {/if}
                {if="!$post->isMicroblog()"}
                    {if="!$public"}
                    <a href="{$c->route('community', $post->server)}">
                    {/if}
                        {$post->server}
                    {if="!$public"}</a>{/if} /
                    {if="!$public"}
                    <a href="{$c->route('community', [$post->server, $post->node])}">
                    {/if}
                        {$post->node}
                    {if="!$public"}</a>{/if} –
                {/if}
                {$post->published|strtotime|prepareDate}
                {if="$post->published != $post->updated"}
                    - <i class="zmdi zmdi-edit"></i> {$post->updated|strtotime|prepareDate}
                {/if}
            </p>
            {if="$post->isBrief()"}
                <p class="normal">
                    {$post->getTitle()|addUrls|addHashtagsLinks|nl2br}
                </p>
            {/if}
        </li>
    </ul>
    {/if}
</header>

{if="$repost"}
    <a href="{$c->route('contact', $post->contact->jid)}">
        <ul class="list active middle">
            <li>
                {$url = $post->contact->getPhoto('s')}
                {if="$url"}
                    <span class="primary icon bubble" style="background-image: url('{$url}');">
                        <i class="zmdi zmdi-loop"></i>
                    </span>
                {else}
                    <span class="primary icon bubble color {$post->contact->jid|stringToColor}">
                        <i class="zmdi zmdi-loop"></i>
                    </span>
                {/if}

                <span class="control icon">
                    <i class="zmdi zmdi-chevron-right"></i>
                </span>

                <p>{$c->__('post.repost', $post->contact->truename)}</p>
                <p>{$c->__('post.repost_profile', $post->contact->truename)}</p>
            </li>
        </ul>
    </a>
{/if}

<section dir="{if="$post->isRTL()"}rtl{else}ltr{/if}">
    <content>
        {if="$post->youtube"}
            <div class="video_embed">
                <iframe src="{$post->youtube->href}" frameborder="0" allowfullscreen></iframe>
            </div>
        {elseif="$post->isShort()"}
            {loop="$post->pictures"}
                <img class="big_picture" type="{$value->type}"
                     src="{$value->href}"/>
            {/loop}
        {/if}
        {$post->getContent()|addHashtagsLinks}
    </content>
</section>

{$c->preparePostReply($post)}

<footer>
    {$c->preparePostLinks($post)}

    {if="$post->pictures && !$post->isBrief() && !$post->isShort()"}
        <ul class="list flex middle">
        {loop="$post->pictures"}
            <li class="block pic">
                <span class="primary icon gray">
                    <i class="zmdi zmdi-image"></i>
                </span>
                <a href="{$value->href}" class="alternate" target="_blank">
                    <img type="{$value->type}" src="{$value->href}"/>
                </a>
            </li>
        {/loop}
        </ul>
    {/if}
    {if="$post->open && !$public"}
        <ul class="list active thick">
            <li>
                <span class="primary icon gray">
                    <i class="zmdi zmdi-portable-wifi"></i>
                </span>
                <p class="line normal">
                    {$c->__('post.public_yes')}
                </p>
                <p>
                    <a target="_blank" href="{$post->openlink->href}">
                        {$c->__('post.public_url')} – {$post->openlink->url.host}
                    </a>
                </p>
            </li>
        </ul>
    {/if}
</footer>

{if="!$public"}
    {if="$commentsdisabled"}
        {$commentsdisabled}
    {else}
        <div id="comments" class="spin"></div>
    {/if}
{/if}

{if="!$external"}
    {$c->preparePreviousNext($post)}
{/if}
</article>
<span class="clear padded"></span>
