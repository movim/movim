<article>
    {if="isset($attachements.pictures)"}
        <div
            class="picture"
            style="background-image: url('{$attachements['pictures'][0]['href']}');"
        >
        </div>
    {/if}
    <header class="paddedbottom">
        <h1>{$post->title}</h1>
        <span>
            <i class="fa fa-sitemap"></i>
            <a href="{$c->route('node', array($post->jid, $post->node))}">
                {$post->node}
            </a>
        </span>
         - 
        <span class="date">
            <i class="fa fa-clock-o"></i>
            {$post->published|strtotime|prepareDate}
        </span>
    </header>

    <section class="content padded">
        {$post->contentcleaned}
    </section>

    <footer>
        <div class="enclosures">
            {if="isset($attachements.links)"}
                {loop="$attachements.links"}
                    <a href="{$value.href}" class="alternate" target="_blank">
                        <img src="http://g.etfv.co/{$value.href}"/>{$value.href}
                    </a>
                {/loop}
            {/if}
            {if="isset($attachements.files)"}
                {loop="$attachements.files"}
                    <a
                        href="{$value.href}"
                        class="enclosure"
                        type="{$value.type}"
                        target="_blank">{$value.href}
                    </a>
                {/loop}
            {/if}
        </div>
    </footer>
</article>
