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
                <a href="{$c->route('node', array($post->jid, $post->node))}">
                    <span class="icon bubble color {$post->node|stringToColor}">{$post->node|firstLetterCapitalize}</span>
                </a>
                {if="$post->title != null"}
                    <span>{$post->title}</span>
                {else}
                    <span>{$c->__('post.default_title')}</span>
                {/if}
                <p>{$post->published|strtotime|prepareDate}</p>
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
                            <span>{$value.href}</span>
                        </a>
                    </li>
                {/loop}
            {/if}
        </ul>
    </footer>
</article>
