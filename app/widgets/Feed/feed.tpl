<div id="feed" class="spacetop tabelem"  title="{$c->__('page.feed')}">
    <div id="feedhead">
    {$c->prepareHead()}
    </div>

    <div class="posthead">
                    
        <a 
            class="button color merged left icon blog" 
            href="{$blog_url}"
            target="_blank">
            {$c->__('page.blog')}
        </a><a 
            class="button color orange merged right icon feed" 
            href="{$feed_url}"
            target="_blank">
            {$c->__('page.feed')} (Atom)
        </a>

        <a 
            class="button color purple icon user oppose" 
            href="{$friend_url}">
            {$c->__('my_posts')}
        </a>
    </div>
    
    
    <div id="feedcontent">
        <div id="feedposts">
            {$feeds}
        </div>
    </div>
</div>
