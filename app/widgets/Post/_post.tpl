{if="$external || $public"}
<article class="block">
{/if}

{if="isset($attachements.pictures)"}
    {if="($public && $post->isPublic()) || !$public"}
        <header
            class="big"
            style="
                background-image: linear-gradient(to bottom, rgba(0,0,0,0.9) 0%, rgba(0,0,0,0.5) 100%), url('{$attachements['pictures'][0]['href']}');">
    {/if}
{else}
<header>
{/if}
    {if="!$external && !$public"}
        <ul class="list middle">
            <li>
                <span class="primary icon active" onclick="MovimTpl.hidePanel(); Post_ajaxClear();">
                    <i class="zmdi zmdi-arrow-back"></i>
                </span>

                {if="$post->isMine() && !$public"}
                    {if="$post->isEditable()"}
                        <span class="control icon active" onclick="Publish_ajaxCreate('{$post->origin}', '{$post->node}', '{$post->nodeid}')" title="{$c->__('button.edit')}">
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
        </div>
    {/if}

    {if="($public && $post->isPublic()) || !$public"}
    <ul class="list thick">
        <li>
            {if="$recycled"}
                {$contact = $recycled}
            {else}
                {$contact = $post->getContact()}
            {/if}

            {if="$post->node == 'urn:xmpp:microblog:0'"}
                {$url = $contact->getPhoto('s')}
                {if="$url"}
                    <span class="icon primary bubble">
                        <a href="{$c->route('contact', $contact->jid)}">
                            <img src="{$url}">
                        </a>
                    </span>
                {else}
                    <span class="icon primary bubble color {$contact->jid|stringToColor}">
                        <a href="{$c->route('contact', $contact->jid)}">
                            <i class="zmdi zmdi-account"></i>
                        </a>
                    </span>
                {/if}
            {else}
                <span class="icon primary bubble color {$post->node|stringToColor}">
                    {$post->node|firstLetterCapitalize}
                </span>
            {/if}
            <p {if="$post->title != null"}title="{$post->title|strip_tags}"{/if}>
                <a  {if="$public"}
                        {if="$post->isMicroblog()"}
                        href="{$c->route('blog', array($post->origin, $post->nodeid))}"
                        {else}
                        href="{$c->route('node', array($post->origin, $post->node, $post->nodeid))}"
                        {/if}
                    {else}
                        href="{$c->route('news', $post->nodeid)}"
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
                    <a href="{$c->route('contact', $contact->jid)}">
                    {/if}
                        <i class="zmdi zmdi-account"></i> {$contact->getTrueName()}
                    {if="!$public"}</a>{/if} –
                {/if}
                {if="$post->node != 'urn:xmpp:microblog:0'"}
                    {$post->origin} /
                    {if="!$public"}
                    <a href="{$c->route('group', array($post->origin, $post->node))}">
                    {/if}
                        <i class="zmdi zmdi-pages"></i> {$post->node}
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
        <section>
            <content>
                {if="$post->isShort() && isset($attachements.pictures)"}
                    {loop="$attachements.pictures"}
                        <a href="{$value.href}" class="alternate" target="_blank">
                            <img class="big_picture" type="{$value.type}" src="{$value.href|urldecode}"/>
                        </a>
                    {/loop}
                {elseif="$post->getYoutube()"}
                    <div class="video_embed">
                        <iframe src="https://www.youtube.com/embed/{$post->getYoutube()}" frameborder="0" allowfullscreen></iframe>
                    </div>
                {/if}
                {$post->contentcleaned}
            </content>
        </section>

        <footer>
            {$tags = $post->getTags()}
            {if="isset($tags)"}
                <ul class="list middle">
                    <li>
                        <span class="primary icon zmdi zmdi-tag gray"></span>
                        <p class="normal">
                            {loop="$tags"}
                                <a target="_blank" href="{$c->route('tag', array($value))}">#{$value}</a>
                            {/loop}
                        </p>
                    </li>
                </ul>
            {/if}
            <ul class="list middle divided spaced">
                {if="isset($attachements.links)"}
                    {loop="$attachements.links"}
                        {if="substr($value.href, 0, 5) != 'xmpp:' && filter_var($value.href, FILTER_VALIDATE_URL)"}
                            <li>
                                <span class="primary icon">
                                    <img src="https://icons.duckduckgo.com/ip2/{$value.url.host}.ico"/>
                                </span>
                                <p class="normal line">
                                    <a href="{$value.href}" class="alternate" target="_blank">
                                        {$value.href|urldecode}
                                    </a>
                                </p>
                            </li>
                        {/if}
                    {/loop}
                {/if}
                {if="isset($attachements.files)"}
                    {loop="$attachements.files"}
                        <li>
                            <span class="primary icon gray">
                                <span class="zmdi zmdi-attachment-alt"></span>
                            </span>
                            <p class="normal line">
                                <a
                                    href="{$value.href}"
                                    class="enclosure"
                                    type="{$value.type}"
                                    target="_blank">
                                {$value.href|urldecode}
                                </a>
                            </p>
                        </li>
                    {/loop}
                {/if}
            </ul>
            {if="!$post->isShort() && isset($attachements.pictures)"}
                <ul class="list flex middle">
                {loop="$attachements.pictures"}
                    <li class="block pic">
                        <span class="primary icon gray">
                            <i class="zmdi zmdi-image"></i>
                        </span>
                        <a href="{$value.href}" class="alternate" target="_blank">
                            <img type="{$value.type}" src="{$value.href|urldecode}"/>
                        </a>
                    </li>
                {/loop}
                </ul>
            {/if}
            {if="$post->isMine() && !$public"}
                <ul class="list middle">
                    <li>
                        <span class="primary icon gray">
                            <i class="zmdi zmdi-portable-wifi"></i>
                        </span>
                        <span class="control">
                            <form>
                                <div class="action">
                                    <div class="checkbox">
                                        <input
                                            type="checkbox"
                                            id="privacy_{$post->nodeid}"
                                            name="privacy_{$post->nodeid}"
                                            {if="$post->privacy"}
                                                checked
                                            {/if}
                                            {if="$external"}
                                                onclick="Group_ajaxTogglePrivacy('{$post->nodeid}')"
                                            {else}
                                                onclick="Post_ajaxTogglePrivacy('{$post->nodeid}')"
                                            {/if}
                                        >
                                        <label for="privacy_{$post->nodeid}"></label>
                                    </div>
                                </div>
                            </form>
                        </span>
                        <p class="line normal">
                            <a target="_blank" href="{$post->getPublicUrl()}">
                                {$c->__('post.public')}
                            </a>
                        </p>
                    </li>
                </ul>
            {/if}
        </footer>

        {if="$recycled"}
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

                        <div class="control">
                            <i class="zmdi zmdi-chevron-right"></i>
                        </div>

                        <p>{$c->__('post.repost', $post->getContact()->getTrueName())}</p>
                        <p>{$c->__('post.repost_profile', $post->getContact()->getTrueName())}</p>
                    </li>
                </ul>
            </a>
        {/if}

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
                            <p>
                                <span class="info">{$value->published|strtotime|prepareDate}</span>
                                {if="$value->title"}
                                    {$value->title}
                                {else}
                                    {$value->contentraw}
                                {/if}
                            </p>
                        </li>
                    {/loop}
                </ul><br />
            {/if}
        {else}
            <div id="comments"></div>
        {/if}
    {/if}
</article>
