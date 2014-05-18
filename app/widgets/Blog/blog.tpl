<div class="tabelem" title="{$c->__('page.feed')}" id="blog" >
    <h1>{$title}</h1>
    <div class="posthead">
        <a 
            class="button color orange icon feed merged left" 
            href="{$feed}"
            target="_blank"
        >
            {$c->__('page.feed')} (Atom)
        </a>
    </div>

    {$posts}
</div>
