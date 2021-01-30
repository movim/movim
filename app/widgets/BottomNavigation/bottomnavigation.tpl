<ul id="bottomnavigation" class="navigation color dark">
    <li onclick="MovimTpl.toggleMenu()">
        <span class="primary icon">
            <i class="material-icons">more_vert</i>
        </span>
    </li>
    {if="$c->getUser()->hasPubsub()"}
        <li {if="$page == 'news'"}class="active"{/if}
            onclick="MovimUtils.reload('{$c->route('news')}')"
            title="{$c->__('page.news')}"
        >
            <span class="primary icon">
                <i class="material-icons">home</i>
                <span data-key="news" class="counter"></span>
            </span>
        </li>
    {/if}
    <li {if="$page == 'explore' || $page == 'community'"}class="active"{/if}
        onclick="MovimUtils.reload('{$c->route('explore')}')"
        title="{$c->__('page.explore')}"
    >
        <span class="primary icon"><i class="material-icons">explore</i></span>
    </li>
    <li onclick="Search_ajaxRequest()"
        title="{$c->__('button.search')}"
    >
        <span class="primary icon">
            <i class="material-icons">search</i>
        </span>
    </li>
    <li onclick="Notifications_ajaxRequest()"
        title="{$c->__('notifs.title')}"
    >
        <span class="primary icon">
            <i class="material-icons">notifications</i>
            <span class="counter notifications"></span>
        </span>
    </li>
    <li {if="$page == 'chat'"}class="active"{/if}
        onclick="MovimUtils.reload('{$c->route('chat')}')"
        title="{$c->__('page.chats')}"
    >
        <span class="primary icon" id="bottomchatcounter">
            {autoescape="off"}
                {$chatCounter}
            {/autoescape}
        </span>
    </li>
</ul>
