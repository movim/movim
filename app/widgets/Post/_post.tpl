<article class="block">
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
        <ul class="thick">
            <li class="condensed">
                {if="$recycled"}
                    {$contact = $recycled}
                {else}
                    {$contact = $post->getContact()}
                {/if}

                {if="$post->node == 'urn:xmpp:microblog:0'"}
                    <a href="{$c->route('contact', $contact->jid)}">
                        {$url = $contact->getPhoto('s')}
                        {if="$url"}
                            <span class="icon bubble">
                                <img src="{$url}">
                            </span>
                        {else}
                            <span class="icon bubble color {$contact->jid|stringToColor}">
                                <i class="zmdi zmdi-account"></i>
                            </span>
                        {/if}
                    </a>
                {else}
                    <a href="{$c->route('group', array($post->origin, $post->node))}">
                        <span class="icon bubble color {$post->node|stringToColor}">{$post->node|firstLetterCapitalize}</span>
                    </a>
                {/if}
                <h2 {if="$post->title != null"}title="{$post->title|strip_tags}"{/if}>
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
                </h2>
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
    </header>
    {if="$public && !$post->isPublic()"}
        <ul class="thick">
            <li>
                <span class="icon color gray bubble">
                    <i class="zmdi zmdi-lock"></i>
                </span>
                <p class="center"> {$c->__('blog.private')} - <a href="{$c->route('main')}">{$c->__('page.login')}</a></p>
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
                <ul class="middle">
                    <li>
                        <span class="icon zmdi zmdi-tag gray"></span>
                        <span>
                            {loop="$tags"}
                                <a target="_blank" href="{$c->route('tag', array($value))}">#{$value}</a>
                            {/loop}
                        </span>
                    </li>
                </ul>
            {/if}
            <ul class="middle divided spaced">
                {if="isset($attachements.links)"}
                    {loop="$attachements.links"}
                        {if="substr($value.href, 0, 5) != 'xmpp:' && filter_var($value.href, FILTER_VALIDATE_URL)"}
                            <li>
                                <span class="icon">
                                    <img src="https://icons.duckduckgo.com/ip2/{$value.url.host}.ico"/>
                                </span>
                                <a href="{$value.href}" class="alternate" target="_blank">
                                    <span>{$value.href|urldecode}</span>
                                </a>
                            </li>
                        {/if}
                    {/loop}
                {/if}
                {if="isset($attachements.files)"}
                    {loop="$attachements.files"}
                        <li>
                            <a
                                href="{$value.href}"
                                class="enclosure"
                                type="{$value.type}"
                                target="_blank">
                                <span class="icon gray">
                                    <span class="zmdi zmdi-attachment-alt"></span>
                                </span>
                                <span>{$value.href|urldecode}</span>
                            </a>
                        </li>
                    {/loop}
                {/if}
            </ul>
            {if="!$post->isShort() && isset($attachements.pictures)"}
                <ul class="flex middle">
                {loop="$attachements.pictures"}
                    <li class="block pic">
                        <span class="icon gray">
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
                <ul class="middle">
                    <li class="action">
                        <form>
                            <div class="action">
                                <div class="checkbox">
                                    <input
                                        type="checkbox"
                                        id="privacy"
                                        name="privacy"
                                        {if="$post->privacy"}
                                            checked
                                        {/if}
                                        {if="$external"}
                                            onclick="Group_ajaxTogglePrivacy('{$post->nodeid}')"
                                        {else}
                                            onclick="Post_ajaxTogglePrivacy('{$post->nodeid}')"
                                        {/if}
                                    >
                                    <label for="privacy"></label>
                                </div>
                            </div>
                        </form>
                        <span class="icon gray">
                            <i class="zmdi zmdi-portable-wifi"></i>
                        </span>
                        <span>
                            <a target="_blank" href="{$post->getPublicUrl()}">
                                {$c->__('post.public')}
                            </a>
                        </span>
                    </li>
                </ul>
            {/if}
        </footer>

        {if="$recycled"}
            <a href="{$c->route('contact', $post->getContact()->jid)}">
                <ul class="active middle">
                    <li class="condensed action">
                        <div class="action">
                            <i class="zmdi zmdi-chevron-right"></i>
                        </div>
                        {$url = $post->getContact()->getPhoto('s')}
                        {if="$url"}
                            <span class="icon bubble" style="background-image: url('{$url}');">
                                <i class="zmdi zmdi-loop"></i>
                            </span>
                        {else}
                            <span class="icon bubble color {$post->getContact()->jid|stringToColor}">
                                <i class="zmdi zmdi-loop"></i>
                            </span>
                        {/if}

                        <span>{$c->__('post.repost', $post->getContact()->getTrueName())}</span>
                        <p>{$c->__('post.repost_profile', $post->getContact()->getTrueName())}</p>
                    </li>
                </ul>
            </a>
        {/if}

        {if="$external"}
            {$comments = $c->getComments($post)}
            {if="$comments"}
                <ul class="spaced middle">
                    <li class="subheader">
                        {$c->__('post.comments')}
                        <span class="info">{$comments|count}</span>
                    </li>
                    {loop="$comments"}
                        <li class="condensed">
                            {$url = $value->getContact()->getPhoto('s')}
                            {if="$url"}
                                <span class="icon bubble">
                                    <img src="{$url}">
                                </span>
                            {else}
                                <span class="icon bubble color {$value->getContact()->jid|stringToColor}">
                                    <i class="zmdi zmdi-account"></i>
                                </span>
                            {/if}
                            <span class="info">{$value->published|strtotime|prepareDate}</span>
                            <span>
                                {$value->getContact()->getTrueName()}
                            </span>
                            <p class="all">
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
