<ul class="navigation list active" dir="ltr">
    {if="$c->me->hasPubsub()"}
        <li onclick="MovimUtils.reload('{$c->route('news')}')"
            class="on_desktop {if="$page == 'news' || $page == 'post'"}active{/if}"
            title="{$c->__('page.news')}"
        >
            <span class="primary icon">
                <i class="material-symbols">home</i>
                <span data-key="news" class="counter"></span>
            </span>
            <div>
                <p class="normal">{$c->__('page.news')}</p>
            </div>
        </li>

        <li onclick="MovimUtils.reload('{$c->route('explore')}')"
            class="on_desktop {if="$page == 'explore' || $page == 'community'"}active{/if}"
            title="{$c->__('page.explore')}"
        >
            <span class="primary icon"><i class="material-symbols">explore</i></span>
            <div>
                <p class="normal">{$c->__('page.explore')}</p>
            </div>
        </li>
    {/if}

    <li onclick="{if="$page == 'chat'"}Rooms.toggleScroll(){else}MovimUtils.reload('{$c->route('chat')}'){/if}"
        class="on_desktop {if="$page == 'chat'"}active{/if}"
        title="{$c->__('page.chats')}"
    >
        <span class="primary icon" id="chatcounter" {if="$chatCounter > 0"}data-counter="{$chatCounter}"{/if}>
            <i class="material-symbols">chat_bubble</i>
        </span>
        <div>
            <p class="normal">{$c->__('page.chats')}</p>
        </div>
    </li>

    <hr class="on_desktop"/>

    {if="$c->me->hasPubsub()"}
        <li onclick="MovimUtils.reload('{$c->route('publish')}')"
            title="{$c->__('page.publish')}"
            {if="$page == 'publish'"}class="active"{/if}
        >
            <span class="primary icon">
                <i class="material-symbols">post_add</i>
            </span>
            <div>
                <p class="normal">{$c->__('page.publish')}</p>
            </div>
        </li>

        {if="$c->me->hasUpload()"}
            <li onclick="PublishStories_ajaxOpen()"
                title="{$c->__('page.publish')}"
            >
                <span class="primary icon">
                    <i class="material-symbols">web_stories</i>
                </span>
                <div>
                    <p class="normal">{$c->__('stories.publish')}</p>
                </div>
            </li>
        {/if}
    {/if}
</ul>

<ul class="navigation list oppose active" dir="ltr">
    <li onclick="Notifications_ajaxRequest()"
        title="{$c->__('notifs.title')}"
        class="on_desktop"
    >
        <span class="primary icon">
            <i class="material-symbols">notifications</i>
            <span class="counter notifications"></span>
        </span>
        <div>
            <p class="normal">{$c->__('notifs.title')}</p>
        </div>
    </li>

    <hr class="on_desktop"/>

    <li onclick="Search_ajaxRequest()"
        title="{$c->__('button.search')}"
        class="on_desktop"
    >
        <span class="primary icon">
            <i class="material-symbols">search</i>
        </span>
        <div>
            <p class="normal">{$c->__('button.search')}</p>
        </div>
    </li>

    {if="$c->me->hasPubsub()"}
        <li onclick="MovimUtils.reload('{$c->route('subscriptions')}')"
            title="{$c->__('communityaffiliation.subscriptions')}"
            {if="$page == 'subscriptions'"}class="active"{/if}
        >
            <span class="primary icon"><i class="material-symbols">bookmarks</i></span>
            <div>
                <p class="normal">{$c->__('communityaffiliation.subscriptions')}</p>
            </div>
        </li>
    {/if}

    <a href="#" class="on_mobile">
        <li onclick="MovimTpl.toggleMenu()">
            <span class="primary icon bubble"><i class="material-symbols">arrow_back</i></span>
            <div>
                <p class="normal">{$c->__('button.close')}</p>
            </div>
        </li>
    </a>
</ul>
