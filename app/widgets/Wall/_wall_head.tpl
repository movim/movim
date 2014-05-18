{if="$start == -1"}
    {$map}
    <div class="posthead spacetop">
        <a 
            class="button color icon blog merged left" 
            href="{$c->route('blog',array($from, 'urn:xmpp:microblog:0'))}"
            target="_blank"
        >
            {$c->__('blog.title')}
        </a><a 
            class="button color orange icon feed merged right" 
            href="{$c->route('feed',array($from, 'urn:xmpp:microblog:0'))}"
            target="_blank"
        >
            {$c->__('feed.title')} (Atom)
        </a>
        
        <a 
            class="button color icon refresh" 
            href="#"
            onclick="{$refresh}
                this.innerHTML = '{$c->__('Updating')}'; 
                this.className= 'button color orange icon merged right loading';
                this.onclick = 'return false;'";
        >
            {$c->__('button.update')}
        </a>
    </div>
{/if}

{$posts}

{if="count($pl) > 9"}
    <div class="block large">
        <div 
            class="older" 
            onclick="{$older} this.parentNode.style.display = 'none'">
            {$c->__('post.older')}
        </div>
    </div>
{/if}
