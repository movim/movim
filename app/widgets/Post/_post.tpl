{if="isset($attachements.pictures)"}
    <header
        class="big"
        style="
            background-image: url('{$attachements['pictures'][0]['href']}');">
    </header>
{/if}

<article>
    <header>
        <ul class="thick">
            <li class="condensed">
                {if="$post->node == 'urn:xmpp:microblog:0'"}
                    <span class="icon bubble">
                        <img src="{$post->getContact()->getPhoto('s')}">
                    </span>
                {else}
                <!--<a href="{$c->route('node', array($post->jid, $post->node))}">-->
                    <span class="icon bubble color {$post->node|stringToColor}">{$post->node|firstLetterCapitalize}</span>
                <!--</a>-->
                {/if}
                <span>
                    {if="$post->title != null"}
                        {$post->title}
                    {else}
                        {$c->__('post.default_title')}
                    {/if}
                </span>
                <p>
                    {if="$post->node == 'urn:xmpp:microblog:0'"}
                        {$post->getContact()->getTrueName()} - 
                    {/if}
                    {$post->published|strtotime|prepareDate}
                </p>
            </li>
        </ul>
    </header>

    <section>
        {$post->contentcleaned}
    </section>

    <footer>
        <ul class="thin">
            {if="isset($attachements.links)"}
                {loop="$attachements.links"}
                    <li>
                        <span class="icon small"><img src="http://icons.duckduckgo.com/ip2/{$value.url.host}.ico"/></span>
                        <a href="{$value.href}" class="alternate" target="_blank">
                            <span>{$value.href}</span>
                        </a>
                    </li>
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
                            <span class="icon small gray">
                                <span class="md md-attach-file"></span>
                            </span>
                            <span>{$value.href}</span>
                        </a>
                    </li>
                {/loop}
            {/if}
        </ul>
        {if="$post->isMine()"}
            <ul class="thick">
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
                                    onclick="Post_ajaxTogglePrivacy('{$post->nodeid}')">
                                <label for="privacy"></label>
                            </div>
                        </div>
                    </form>
                    <span class="icon bubble color red">
                        <i class="md md-public"></i>
                    </span>
                    <span>{$c->__('post.public')}</span>
                </li>
            </ul>
        {/if}
    </footer>

    <div id="comments"></div>
</article>
