<article class="block">
<header class="relative">
    {$c->preparePostHeader($post, $repost, $public)}
</header>

{if="$repost"}
    <a href="{$c->route('contact', $post->contact->jid)}">
        <ul class="list active middle">
            <li>
                {$url = $post->contact->getPhoto('s')}
                {if="$url"}
                    <span class="primary icon bubble" style="background-image: url('{$url}');">
                        <i class="material-icons">loop</i>
                    </span>
                {else}
                    <span class="primary icon bubble color {$post->contact->jid|stringToColor}">
                        <i class="material-icons">loop</i>
                    </span>
                {/if}

                <span class="control icon">
                    <i class="material-icons">chevron_right</i>
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
                    <i class="material-icons">image</i>
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
                    <i class="material-icons">wifi_tethering</i>
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

    {if="$public"}
        <div id="comments">
            {$c->prepareComments($post, true)}
        </div>
    {else}
        {if="$commentsdisabled"}
            {$commentsdisabled}
        {else}
            <div id="comments" class="spin"></div>
        {/if}

        {$c->preparePreviousNext($post)}
    {/if}

</footer>

</article>
<span class="clear padded"></span>
