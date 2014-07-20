<div id="feed" class="spacetop tabelem"  title="{$c->__('page.feed')}">
    <div id="feedhead">
    {$c->prepareHead()}
    </div>

    <div class="posthead paddedbottom">
        <a 
            class="button color merged left" 
            href="{$blog_url}"
            target="_blank">
            <i class="fa fa-pencil"></i> {$c->__('page.blog')}
        </a><a 
            class="button color orange merged right" 
            href="{$feed_url}"
            target="_blank">
            <i class="fa fa-rss"></i> {$c->__('page.feed')} (Atom)
        </a>

        <a 
            class="button color purple oppose" 
            href="{$friend_url}">
            <i class="fa fa-user"></i> {$c->__('my_posts')}
        </a>
    </div>
    
    <div id="feedcontent">
        <div id="feedposts">
            {$feeds}
        </div>
    </div>
</div>
