<ul id="navigation" class="list active" dir="ltr">
    {if="$c->getUser()->hasPubsub()"}
    <a class="classic"
       href="{$c->route('publish')}"
       title="{$c->__('page.publish')}">
        <li {if="$page == 'publish'"}class="active"{/if}>
            <span class="primary icon">
                <i class="material-icons">post_add</i>
            </span>
            <div>
                <p class="normal">{$c->__('page.publish')}</p>
            </div>
        </li>
    </a>
    <hr />
    <a href="{$c->route('news')}"
       class="on_desktop"
       title="{$c->__('page.news')}">
        <li {if="$page == 'news' || $page == 'post'"}class="active"{/if}>
            <span class="primary icon">
                <i class="material-icons">home</i>
                <span data-key="news" class="counter"></span>
            </span>
            <div>
                <p class="normal">{$c->__('page.news')}</p>
            </div>
        </li>
    </a>
    {/if}
    <a href="{$c->route('explore')}"
       class="on_desktop"
       title="{$c->__('page.explore')}">
        <li {if="$page == 'explore' || $page == 'community'"}class="active"{/if}>
            <span class="primary icon"><i class="material-icons">explore</i></span>
            <div>
                <p class="normal">{$c->__('page.explore')}</p>
            </div>
        </li>
    </a>
    <a href="{$c->route('chat')}"
       id="chatbutton"
       class="on_desktop"
       title="{$c->__('page.chats')}">
        <li {if="$page == 'chat'"}class="active"{/if}>
            <span class="primary icon" id="chatcounter">
                {autoescape="off"}
                    {$chatCounter}
                {/autoescape}
            </span>
            <div>
                <p class="normal">{$c->__('page.chats')}</p>
            </div>
        </li>
    </a>
</ul>

<ul class="list oppose active" dir="ltr">
    <li onclick="Search_ajaxRequest()"
    title="{$c->__('button.search')}"
    >
        <span class="primary icon">
            <i class="material-icons">search</i>
        </span>
        <div>
            <p class="normal">{$c->__('button.search')}</p>
        </div>
    </li>

    <hr />

    <a href="#">
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
    </a>
    <a href="#">
        <li onclick="Notifications_ajaxRequest()"
            title="{$c->__('notifs.title')}"
        >
            <span class="primary icon">
                <i class="material-icons">notifications</i>
                <span class="counter notifications"></span>
            </span>
            <div>
                <p class="normal">{$c->__('notifs.title')}</p>
            </div>
        </li>
    </a>

    {if="$c->getUser()->hasPubsub()"}
    <a href="{$c->route('subscriptions')}"
       title="{$c->__('communityaffiliation.subscriptions')}">
        <li {if="$page == 'subscriptions'"}class="active"{/if}>
            <span class="primary icon"><i class="material-icons">bookmarks</i></span>
            <div>
                <p class="normal">{$c->__('communityaffiliation.subscriptions')}</p>
            </div>
        </li>
    </a>
    {/if}

    <a class="classic on_mobile" href="#">
        <li onclick="MovimTpl.toggleMenu()">
            <span class="primary icon bubble"><i class="material-icons">arrow_back</i></span>
            <div>
                <p class="normal">{$c->__('button.close')}</p>
            </div>
        </li>
    </a>
</ul>
