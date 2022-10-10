<article class="block">
<header>
    {if="!$public"}
        <ul class="list middle">
            <li>
                <span class="primary icon gray active" onclick="history.back();">
                    <i class="material-icons">arrow_back</i>
                </span>

                {if="$post->isMine()"}
                    {if="$post->isEditable()"}
                        <span class="control icon active gray"
                              onclick="MovimUtils.redirect('{$c->route('publish', [$post->server, $post->node, $post->nodeid])}')"
                              title="{$c->__('button.edit')}">
                            <i class="material-icons">edit</i>
                        </span>
                    {/if}
                    <span class="control icon active gray"
                          onclick="PostActions_ajaxDelete('{$post->server}', '{$post->node}', '{$post->nodeid}')"
                          title="{$c->__('button.delete')}">
                        <i class="material-icons">delete</i>
                    </span>
                {/if}

                <div>
                    <p class="line">
                        {if="$post->title != null && !$post->isBrief()"}
                            {autoescape="off"}
                                {$post->getTitle()}
                            {/autoescape}
                        {else}
                            {$c->__('post.default_title')}
                        {/if}
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
            {if="$post->nsfw"}
                <span class="primary icon bubble color red tiny">
                    +18
                </span>
            {elseif="$post->isMicroblog()"}
                {if="$post->contact"}
                    {$url = $contact->getPhoto('m')}

                    {if="$url"}
                        <span class="icon primary bubble">
                            <a href="#" onclick="Post_ajaxGetContact('{$contact->jid}')">
                                <img src="{$url}">
                            </a>
                        </span>
                    {else}
                        <span class="icon primary bubble color {$contact->jid|stringToColor}">
                            <a href="#" onclick="Post_ajaxGetContact('{$contact->jid}')">
                                <i class="material-icons">person</i>
                            </a>
                        </span>
                    {/if}
                {else}
                    <span class="icon primary bubble color {$post->aid|stringToColor}">
                        <a href="#" onclick="Post_ajaxGetContact('{$post->aid}')">
                            <i class="material-icons">person</i>
                        </a>
                    </span>
                {/if}
            {else}
                {$url = null}
                {if="$info != null"}
                    {$url = $info->getPhoto('l')}
                {/if}
                {if="$url"}
                    <span class="primary icon bubble">
                        <img src="{$url}"/>
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
                    <a
                    {if="$public"}
                        {if="$post->isMicroblog()"}
                            href="{$c->route('blog', [$post->server, $post->nodeid])}"
                        {else}
                            href="{$c->route('node', [$post->server, $post->node, $post->nodeid])}"
                        {/if}
                    {else}
                        href="{$c->route('post', [$post->server, $post->node, $post->nodeid])}"
                    {/if}
                    >
                        <i class="material-icons">chevron_right</i>
                    </a>
                </span>
            {/if}
            <div>
                {if="!$post->isBrief()"}
                    <p {if="$post->title != null"}title="{$post->title|strip_tags}"{/if}>
                        {autoescape="off"}
                            {$post->getTitle()|addHashtagsLinks|addEmojis}
                        {/autoescape}
                    </p>
                {else}
                    <p></p>
                {/if}
                <p>
                    {if="$contact"}
                        {if="!$public"}
                        <a href="#" onclick="if (typeof Post_ajaxGetContact == 'function') { Post_ajaxGetContact('{$contact->jid}'); } else { Group_ajaxGetContact('{$contact->jid}'); } ">
                        {/if}
                        {$contact->truename}
                        {if="!$public"}</a>{/if} ·
                    {elseif="$post->aname"}
                        {$post->aname} ·
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
                        {if="!$public"}</a>{/if} ·
                    {/if}
                    {$post->published|strtotime|prepareDate}
                    {if="$post->published != $post->updated"}
                        <i class="material-icons" title="{$post->updated|strtotime|prepareDate}">
                            edit
                        </i>
                    {/if}
                    {if="$post->contentcleaned && readTime($post->contentcleaned)"}
                        · {$post->contentcleaned|readTime}
                    {/if}
                </p>
                {if="$post->isBrief()"}
                    <p class="normal">
                        {autoescape="off"}
                            {$post->getTitle()|addUrls|addHashtagsLinks|nl2br|prepareString|addEmojis}
                        {/autoescape}
                    </p>
                {/if}
            </div>
        </li>
    </ul>
{/if}

{if="$repost"}
    <a href="{$c->route('contact', $post->contact->jid)}">
        <ul class="list active middle">
            <li>
                {$url = $post->contact->getPhoto('m')}
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

                <div>
                    <p>{$c->__('post.repost', $post->contact->truename)}</p>
                    <p>{$c->__('post.repost_profile', $post->contact->truename)}</p>
                </div>
            </li>
        </ul>
    </a>
{/if}

<section dir="{if="$post->isRTL()"}rtl{else}ltr{/if}">
    <div>
        {if="$post->youtube"}
            <div class="video_embed">
                <iframe src="{$post->youtube->href}" frameborder="0" allowfullscreen></iframe>
            </div>
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
            {$post->getContent()|addHashtagsLinks}
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
                            <a class="chip outline" href="{$c->route('tag', $value->name)}">
                                <i class="material-icons icon gray">tag</i>{$value->name}
                            </a>
                        {/loop}
                    </p>
                </div>
            </li>
        </ul>
    {/if}

    {if="$post->openlink && $post->openlink->url && (!defined('BASE_HOST') || $post->openlink->url.host != BASE_HOST)"}
        <ul class="list middle flex active">
            <li class="block large" onclick="MovimUtils.openInNew('{$post->openlink->href}')">
                <span class="primary icon gray">
                    <i class="material-icons">wifi_tethering</i>
                </span>
                <span class="control icon gray">
                    <i class="material-icons">open_in_new</i>
                </span>
                <div>
                    <p class="line normal">
                        {$c->__('post.public_yes')}
                    </p>
                    <p class="line">
                        <a href="#">{$post->openlink->url.host}</a>
                        {if="$post->openlink->url.path != '/'"}
                            <span class="second sticked">{$post->openlink->url.path}</span>
                        {/if}
                    </p>
                </div>
            </li>
        </ul>
    {/if}

    {if="$post->pictures()->count() > 0 && !$post->isBrief() && !$post->isShort()"}
        <ul class="list">
            <li class="subheader">
                <div>
                    <p>
                        {$c->__('general.pictures')}
                        <span class="second">
                            {$post->pictures()->count()}
                            <i class="material-icons">image</i>
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
                    <i class="material-icons">visibility</i>
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
