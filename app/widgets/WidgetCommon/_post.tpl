<span id="{$idhash}"></span>
<article class="block" id="{$id}">
    <div class="{$access}" title="{$flagtitle}"></div>
    <header>
        <a href="{$friend}">
            {$avatar}
        </a>
        <span class="title">{$title}</span>
        <span class="contact">{$contact}</span>
        <span class="date">{$date}</span>
    </header>
    <section class="content {if="$spoiler != false"}spoiler{/if}" onclick="{$spoiler}">
        {$content}
    </section>

    <footer>
        {$tags}
        {$enc}
        {$comments}
        {$place}
        {$recycle}
        {$group}
        {$toolbox}
    </footer>
</article>
