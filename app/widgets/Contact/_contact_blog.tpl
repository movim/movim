<ul class="tabs">
    <li href="#" onclick="Contact_ajaxGetContact('{$jid}')" title="{$c->__('general.legend')}">
        <span>{$c->__('page.profile')}</span>
    </li>
    <li class="active" onclick="Contact_ajaxGetBlog('{$jid}')" title="{$c->__('page.blog')}">
        <span>{$c->__('page.blog')}</span>
    </li>
    <li onclick="Contact_ajaxGetGallery('{$jid}')" title="{$c->__('page.gallery')}">
        <span>{$c->__('page.gallery')}</span>
    </li>
</ul>

{if="$blog != null"}
    <ul class="list middle active card block flex">
        <li class="block large subheader">
            <p>{$c->__('blog.last')}</p>
        </li>
        {loop="$blog"}
            <li class="block" onclick="MovimUtils.reload('{$c->route('post', [$value->origin, $value->node, $value->nodeid])}')">
                {$picture = $value->getPicture()}
                {if="$picture != null"}
                    <span class="primary icon thumb color white" style="background-image: linear-gradient(to bottom, rgba(0, 0, 0, 0.7) 0%, rgba(0, 0, 0, 0.3) 100%), url({$picture});">
                        <i title="{$c->__('menu.public')}" class="zmdi zmdi-portable-wifi"></i>
                    </span>
                {else}
                    {$url = $value->getContact()->getPhoto('l')}
                    {if="$url"}
                        <span class="primary icon thumb color white" style="background-image: linear-gradient(to bottom, rgba(0, 0, 0, 0.7) 0%, rgba(0, 0, 0, 0.3) 100%), url({$url});">
                            <i title="{$c->__('menu.public')}" class="zmdi zmdi-portable-wifi"></i>
                        </span>
                    {else}
                        <span class="primary icon thumb color {$value->getContact()->jid|stringToColor}">
                            <i title="{$c->__('menu.public')}" class="zmdi zmdi-portable-wifi"></i>
                        </span>
                    {/if}
                {/if}
                {if="$value->title != null"}
                    <p class="line">{$value->title}</p>
                {else}
                    <p class="line">{$c->__('hello.contact_post')}</p>
                {/if}

                <p>{$value->getSummary()}</p>
                <p>
                    {$count = $value->countLikes()}
                    {if="$count > 0"}
                        {$count} <i class="zmdi zmdi-favorite-outline"></i>
                    {/if}

                    {$count = $value->countComments()}
                    {if="$count > 0"}
                        {$count} <i class="zmdi zmdi-comment-outline"></i>
                    {/if}
                    <span class="info">{$value->published|strtotime|prepareDate}</span>
                </p>
            </li>
        {/loop}
        <a href="{$c->route('blog', array($contact->jid))}" target="_blank" class="block large simple">
            <li>
                <span class="primary icon">
                    <i class="zmdi zmdi-portable-wifi"></i>
                </span>
                <span class="control icon">
                    <i class="zmdi zmdi-chevron-right"></i>
                </span>
                <p class="normal line">{$c->__('blog.visit')}</p>
            </li>
        </a>
    </ul>
{else}
    <div class="placeholder icon blog">
        <h4>{$c->__('post.empty')}</h4>
    </div>
{/if}
