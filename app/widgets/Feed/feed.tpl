<div id="feed" class="spacetop tabelem"  title="{$c->t('Feed')}">
    <div id="feedhead">
    {$c->prepareHead()}
    </div>

    <div class="posthead">
                    
        <a 
            class="button color merged left icon blog" 
            href="{$blog_url}"
            target="_blank">
            {$c->t('Blog')}
        </a><a 
            class="button color orange merged right icon feed" 
            href="{$feed_url}"
            target="_blank">
            {$c->t('Feed')} (Atom)
        </a>

        <a 
            class="button color purple icon user oppose" 
            href="{$friend_url}">
            {$c->t('My Posts')}
        </a>
    </div>
    
    
    <div id="feedcontent">
        <div id="feedposts">
            {$feeds}
        </div>
    </div>
</div>
