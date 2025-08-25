<article class="block">
<header>
    {if="!$public"}
        <ul class="list middle">
            <li>
                <span class="primary icon gray active" onclick="history.back();">
                    <i class="material-symbols">arrow_back</i>
                </span>

                {if="$post->isMine() || ($post->userAffiliation && $post->userAffiliation->affiliation == 'owner')"}
                    {if="$post->isEditable()"}
                        <span class="control icon active gray"
                              onclick="MovimUtils.reload('{$c->route('publish', [$post->server, $post->node, $post->nodeid])}')"
                              title="{$c->__('button.edit')}">
                            <i class="material-symbols">edit</i>
                        </span>
                    {/if}
                    <span class="control icon active gray"
                          onclick="PostActions_ajaxDelete('{$post->server}', '{$post->node}', '{$post->nodeid}')"
                          title="{$c->__('button.delete')}">
                        <i class="material-symbols">delete</i>
                    </span>
                {/if}

                <div>
                    <p class="line" title="{$post->title}">
                        {autoescape="off"}
                            {$post->title}
                        {/autoescape}
                    </p>
                </div>
            </li>
        </ul>
    {/if}
</header>

{if="($public && $post->open) || !$public"}
    <ul class="list thick">
        <li>
            {if="$repost"}
                {$contact = $repost}
            {else}
                {$contact = $post->contact}
            {/if}
            {if="$post->isMicroblog()"}
                {if="$post->contact"}
                    <span class="icon primary bubble">
                        <a href="#" onclick="Post_ajaxGetContact('{$contact->jid}')">
                            <img src="{$contact->getPicture(\Movim\ImageSize::M)}">
                        </a>
                    </span>
                {else}
                    <span class="icon primary bubble color {$post->color}">
                        <a href="#" onclick="Post_ajaxGetContact('{$post->aid}')">
                            <i class="material-symbols">person</i>
                        </a>
                    </span>
                {/if}
            {else}
                {if="$post->info != null"}
                    <span class="primary icon bubble active"
                        onclick="MovimUtils.reload('{$c->route('community', [$post->server, $post->node])}')"
                    >
                        <img src="{$post->info->getPicture(\Movim\ImageSize::L)}"/>
                    </span>
                {else}
                    <span class="primary icon bubble color {$post->color} active"
                        onclick="MovimUtils.reload('{$c->route('community', [$post->server, $post->node])}')"
                    >
                        {$post->node|firstLetterCapitalize}
                    </span>
                {/if}
            {/if}
            {if="$public"}
                <span class="control icon active">
                    <a href="{$post->getLink(true)}">
                        <i class="material-symbols">chevron_right</i>
                    </a>
                </span>
            {/if}
            <div>
                {if="!$post->isBrief()"}
                    <p {if="$post->title != null"}title="{$post->title|strip_tags}"{/if}>
                        {autoescape="off"}
                            {$post->title|addHashtagsLinks|addEmojis}
                        {/autoescape}
                    </p>
                {else}
                    <p></p>
                {/if}
                <p title="{$post->published|prepareDate}">
                    {if="$contact"}
                        {if="!$public"}
                            {if="!$post->isMicroblog()"}
                                <span class="icon bubble tiny">
                                    <img src="{$contact->getPicture()}">
                                </span>
                            {/if}
                            <a href="#" onclick="if (typeof Post_ajaxGetContact == 'function') { Post_ajaxGetContact('{$contact->jid}'); } else { Group_ajaxGetContact('{$contact->jid}'); } ">
                        {/if}
                            {$contact->truename}
                        {if="!$public"}</a>{/if} •
                    {elseif="$post->aname"}
                        {$post->aname} •
                    {/if}
                    {if="!$post->isMicroblog()"}
                        {if="!$public"}
                        <a href="#" onclick="MovimUtils.reload('{$c->route('community', $post->server)}')">
                        {/if}
                            {$post->server}
                        {if="!$public"}</a>{/if} /
                        {if="!$public"}
                        <a href="#" onclick="MovimUtils.reload('{$c->route('community', [$post->server, $post->node])}')">
                        {/if}
                            {$post->node}
                        {if="!$public"}</a>{/if} •
                    {/if}
                    {$post->published|prepareDate:true,true}
                    {if="$post->isEdited()"}
                        <i class="material-symbols" title="{$post->updated|prepareDate}">
                            edit
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
                    <p class="normal brief">
                        {autoescape="off"}
                            {$post->title|addUrls|addHashtagsLinks|nl2br|prepareString|addEmojis}
                        {/autoescape}
                    </p>
                {/if}
            </div>
        </li>
    </ul>
{/if}

{if="$repost"}
    <ul class="list active middle">
        <li onclick="MovimUtils.reload('{$c->route('contact', $post->contact->jid)}');">
            <span class="primary icon bubble" style="background-image: url('{$post->contact->getPicture(\Movim\ImageSize::M)}');">
                <i class="material-symbols">loop</i>
            </span>
            <span class="control icon">
                <i class="material-symbols">chevron_right</i>
            </span>

            <div>
                <p>{$c->__('post.repost', $post->contact->truename)}</p>
                <p>{$c->__('post.see_profile', $post->contact->truename)}</p>
            </div>
        </li>
    </ul>
{/if}

<section dir="{if="$post->isRTL()"}rtl{else}ltr{/if}">
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
                     src="{$value->href}"
                     {if="!empty($value->title)"}
                         title="{$value->title}"
                         alt="{$value->title}"
                     {/if}
                />
            {/loop}
        {/if}
        {autoescape="off"}
            {$post->getContent(true)}
        {/autoescape}
    </div>
</section>

{autoescape="off"}
    {$c->preparePostReply($post)}
{/autoescape}

<footer>
    {autoescape="off"}
        {$c->preparePostLinks($post)}
    {/autoescape}

    {if="$post->tags()->count() > 0"}
        <ul class="list">
            <li>
                <div>
                    <p class="normal">
                        {loop="$post->tags()->get()"}
                            <a class="chip outline active" href="#" onclick="MovimUtils.reload('{$c->route('tag', $value->name)}')">
                                <i class="material-symbols icon gray">tag</i>{$value->name}
                            </a>
                        {/loop}
                    </p>
                </div>
            </li>
        </ul>
    {/if}

    {if="!$public && $post->openlink && $post->openlink->url && (!defined('BASE_HOST') || $post->openlink->url.host != BASE_HOST)"}
        <ul class="list middle flex">
            <li class="block large">
                <span class="primary icon gray">
                    <i class="material-symbols">wifi_tethering</i>
                </span>
                <span class="control icon gray active" onclick="Preview.copyToClipboard('{$post->openlink->href}')">
                    <i class="material-symbols">content_copy</i>
                </span>
                <span class="control icon gray active" onclick="MovimUtils.openInNew('{$post->openlink->href}')">
                    <i class="material-symbols">open_in_new</i>
                </span>
                <div>
                    <p class="line normal">
                        {$c->__('post.public_yes')}
                    </p>
                    <p class="line">
                        <a href="#">{$post->openlink->url.host}</a>{if="array_key_exists('path', $post->openlink->url) && $post->openlink->url.path != '/'"}<span class="second sticked">{$post->openlink->url.path}</span>{/if}
                    </p>
                </div>
            </li>
        </ul>
    {/if}

    {if="$post->pictures->count() > 0 && !$post->isBrief() && !$post->isShort()"}
        <ul class="list">
            <li class="subheader">
                <div>
                    <p>
                        {$c->__('general.pictures')}
                        <span class="second">
                            {$post->pictures->count()}
                            <i class="material-symbols">image</i>
                        </span>
                    </p>
                </div>
            </li>
        </ul>
        <ul class="grid active">
            {loop="$post->pictures"}
                <li
                    {if="isset($value->title)"}
                        title="{$value->title}"
                    {/if}
                    {if="$public"}
                        style="background-image: url('{$value->href}')"
                    {else}
                        style="background-image: url('{$value->href|protectPicture}')"
                    {/if}
                    onclick="Preview_ajaxHttpShow('{$value->href}')"
                >
                    <i class="material-symbols">visibility</i>
                </li>
            {/loop}
        </ul>
    {/if}

    {if="$public"}
        <div id="comments">
            {autoescape="off"}
                {$c->prepareComments($post, true)}
            {/autoescape}
        </div>
    {else}
        {if="$commentsdisabled"}
            {autoescape="off"}
                {$commentsdisabled}
            {/autoescape}
        {else}
            <div id="comments" class="spin"></div>
        {/if}

        {autoescape="off"}
            {$c->preparePreviousNext($post)}
        {/autoescape}
    {/if}

</footer>

</article>
