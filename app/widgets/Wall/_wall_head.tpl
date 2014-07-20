{if="$start == -1"}
    {$map}
    <div class="posthead paddedbottom">
        <a 
            class="button color merged left" 
            href="{$c->route('blog',array($from, 'urn:xmpp:microblog:0'))}"
            target="_blank"
        >
            <i class="fa fa-pencil"></i> {$c->__('blog.title')}
        </a><a 
            class="button color orange merged right" 
            href="{$c->route('feed',array($from, 'urn:xmpp:microblog:0'))}"
            target="_blank"
        >
            <i class="fa fa-rss"></i> {$c->__('feed.title')} (Atom)
        </a>
        
        <a 
            class="button color blue alone" 
            href="#"
            onclick="{$refresh}
                this.className= 'button color orange alone';
                this.onclick = 'return false;'";
        >
            <i class="fa fa-refresh"></i>
        </a>
    </div>
{/if}

{$posts}

{if="count($pl) > 9"}
    <div class="block large">
        <div 
            class="older" 
            onclick="{$older} this.parentNode.style.display = 'none'">
            <i class="fa fa-history"></i> {$c->__('post.older')}
        </div>
    </div>
{/if}
