<span id="{$idhash}"></span>
<!--<div class="post {$class}" id="{$id}">
    <div class="{$access}" title="{$flagtitle}"></div>
    <a href="{$friend}">
        {$avatar}
    </a>

    <div id="{$id}bubble" class="postbubble">
        <div class="header">
            <span class="title">{$title}</span>
            {$contact}
            <span class="date">
                {$date}
            </span>
        </div>
        <div class="content
            {if="$spoiler != false"}
                spoiler
            {/if}
        " onclick="{$spoiler}">
        {$content}
        </div>
        {$tags}
        {$enc}
        {$comments}
        {$place}
        {$recycle}
        {$group}
        {$toolbox}
    </div>  
            
</div>
-->
<article class="block" id="{$id}">
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
