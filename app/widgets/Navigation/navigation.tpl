<ul class="navigation list active" dir="ltr">
    {if="$c->getUser()->hasPubsub()"}
        <li onclick="MovimUtils.reload('{$c->route('publish')}')"
            title="{$c->__('page.publish')}"
            {if="$page == 'publish'"}class="active"{/if}
        >
            <span class="primary icon">
                <i class="material-icons">post_add</i>
            </span>
            <div>
                <p class="normal">{$c->__('page.publish')}</p>
            </div>
        </li>

        <li onclick="MovimUtils.reload('{$c->route('news')}')"
            class="on_desktop {if="$page == 'news' || $page == 'post'"}active{/if}"
            title="{$c->__('page.news')}"
        >
            <span class="primary icon">
                <i class="material-icons">home</i>
                <span data-key="news" class="counter"></span>
            </span>
            <div>
                <p class="normal">{$c->__('page.news')}</p>
            </div>
        </li>
    {/if}

    <li onclick="MovimUtils.reload('{$c->route('explore')}')"
        class="on_desktop {if="$page == 'explore' || $page == 'community'"}active{/if}"
        title="{$c->__('page.explore')}"
    >
        <span class="primary icon"><i class="material-icons">explore</i></span>
        <div>
            <p class="normal">{$c->__('page.explore')}</p>
        </div>
    </li>

    <li onclick="MovimUtils.reload('{$c->route('chat')}')"
        class="on_desktop {if="$page == 'chat'"}active{/if}"
        title="{$c->__('page.chats')}"
    >
        <span class="primary icon" id="chatcounter">
            {autoescape="off"}
                {$chatCounter}
            {/autoescape}
        </span>
        <div>
            <p class="normal">{$c->__('page.chats')}</p>
        </div>
    </li>
</ul>

<ul class="navigation list oppose active" dir="ltr">
    <li onclick="Notifications_ajaxRequest()"
        title="{$c->__('notifs.title')}"
        class="on_desktop"
    >
        <span class="primary icon">
            <i class="material-icons">notifications</i>
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
            <i class="material-icons">search</i>
        </span>
        <div>
            <p class="normal">{$c->__('button.search')}</p>
        </div>
    </li>

    <li onclick="Location_ajaxToggle()"
        title="{$c->__('location.title')}"
        id="location_widget">
        <span class="primary icon disabled">
            <i class="material-icons">place</i>
        </span>
        <div>
            <p class="normal line">{$c->__('location.title')}</p>
        </div>
    </li>

    {if="$c->getUser()->hasPubsub()"}
        <li onclick="MovimUtils.reload('{$c->route('subscriptions')}')"
            title="{$c->__('communityaffiliation.subscriptions')}"
            {if="$page == 'subscriptions'"}class="active"{/if}
        >
            <span class="primary icon"><i class="material-icons">bookmarks</i></span>
            <div>
                <p class="normal">{$c->__('communityaffiliation.subscriptions')}</p>
            </div>
        </li>
    {/if}

    <a href="#" class="on_mobile">
        <li onclick="MovimTpl.toggleMenu()">
            <span class="primary icon bubble"><i class="material-icons">arrow_back</i></span>
            <div>
                <p class="normal">{$c->__('button.close')}</p>
            </div>
        </li>
    </a>
</ul>
