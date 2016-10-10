<ul class="tabs">
    <li href="#" onclick="Contact_ajaxGetContact('{$jid}')" title="{$c->__('general.legend')}">
        <i class="icon zmdi zmdi-account-box-o"></i> {$c->__('page.profile')}
    </li>
    <li onclick="Contact_ajaxGetBlog('{$jid}')" title="{$c->__('page.blog')}">
        <i class="icon zmdi zmdi-edit"></i> {$c->__('page.blog')}
    </li>
    <li class="active" onclick="Contact_ajaxGetGallery('{$jid}')" title="{$c->__('page.gallery')}">
        <i class="icon zmdi zmdi-collection-image-o"></i> {$c->__('page.gallery')}
    </li>
</ul>

{if="isset($gallery)"}
    <ul class="grid active padded">
        {loop="$gallery"}
            <li style="background-image: url('{$value->picture}');"
                onclick="MovimUtils.reload('{$c->route('news', [$value->origin, $value->node, $value->nodeid])}')">
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
