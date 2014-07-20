<div class="tabelem" title="{$c->__('page.feed')}" id="blog" >
    <h1 class="paddedtopbottom">{$title}</h1>
    <div class="posthead paddedtopbottom">
        <a 
            class="button color orange merged left" 
            href="{$feed}"
            target="_blank"
        >
            <i class="fa fa-rss"></i> {$c->__('page.feed')} (Atom)
        </a>
    </div>

    {$posts}

    <div class="spacetop clear"></div>
</div>
