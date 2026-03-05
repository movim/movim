<ul class="navigation list oppose active" dir="ltr">
    {if="$c->me->hasPubsub()"}
        <li onclick="MovimUtils.reload('{$c->route('news')}')"
            class="on_desktop {if="$page == 'news' || $page == 'post'"}active{/if}"
            title="{$c->__('page.news')}"
        >
            <span class="primary icon">
                <i class="material-symbols">newsmode</i>
                <span data-key="news" class="counter"></span>
            </span>
            <div>
                <p>{$c->__('page.news')}</p>
            </div>
        </li>

        <li onclick="MovimUtils.reload('{$c->route('explore')}')"
            class="on_desktop {if="$page == 'explore' || $page == 'community'"}active{/if}"
            title="{$c->__('page.explore')}"
        >
            <span class="primary icon"><i class="material-symbols">explore</i></span>
            <div>
                <p>{$c->__('page.explore')}</p>
            </div>
        </li>
    {/if}
    {if="$c->me->hasPubsub()"}
        <li onclick="Navigation_ajaxHttpPublish()"
            class="on_desktop publish_something"
            title="{$c->__('post.publish_something')}"
        >
            <span class="primary icon"><i class="material-symbols">note_stack_add</i></span>
            <div>
                <p>{$c->__('post.publish_something')}</p>
            </div>
        </li>
    {/if}
</ul>
<hr class="on_desktop"/>
<ul class="navigation list active on_desktop" dir="ltr">
    <li onclick="Search_ajaxRequest()"
        title="{$c->__('button.search')}"
    >
        <span class="primary icon">
            <i class="material-symbols">search</i>
        </span>
        <div>
            <p>{$c->__('button.search')}</p>
        </div>
    </li>

    <li onclick="Notifications_ajaxRequest()"
        title="{$c->__('notifs.title')}"
    >
        <span class="primary icon">
            <i class="material-symbols">notifications</i>
            <span class="counter notifications"></span>
        </span>
        <div>
            <p>{$c->__('notifs.title')}</p>
        </div>
    </li>
</ul>