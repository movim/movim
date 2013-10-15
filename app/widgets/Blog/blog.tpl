<div class="tabelem" title="{$c->t('Feed')}" id="blog" >
    <h1>{$title}</h1>
    <div class="posthead">
        <a 
            class="button color orange icon feed merged left" 
            href="{$feed}"
            target="_blank"
        >
            {$c->t('Feed')} (Atom)
        </a>
    </div>

    {$posts}
</div>
