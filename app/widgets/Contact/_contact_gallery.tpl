<ul class="tabs">
    <li href="#" onclick="Contact_ajaxGetContact('{$jid}')" title="{$c->__('general.legend')}">
        <span>{$c->__('page.profile')}</span>
    </li>
    <li onclick="Contact_ajaxGetBlog('{$jid}')" title="{$c->__('page.blog')}">
        <span>{$c->__('page.blog')}</span>
    </li>
    <li class="active" onclick="Contact_ajaxGetGallery('{$jid}')" title="{$c->__('page.gallery')}">
        <span>{$c->__('page.gallery')}</span>
    </li>
</ul>

{if="isset($gallery)"}
    <ul class="grid active padded">
        {loop="$gallery"}
            <li style="background-image: url('{$value->picture}');"
                onclick="MovimUtils.reload('{$c->route('post', [$value->origin, $value->node, $value->nodeid])}')">
                <nav>
                    <span>{$value->title}</span>
                </nav>
            </li>
        {/loop}
    </ul>
{else}
    <div class="placeholder icon gallery">
        <h4>{$c->__('post.empty')}</h4>
    </div>
{/if}
