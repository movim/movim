<ul id="bottomnavigation" class="navigation color dark">
    <li onclick="MovimTpl.toggleMenu()">
        <span class="primary icon"><i class="material-icons">menu</i></span>
    </li>
    {if="$c->getUser()->hasPubsub()"}
        <li {if="$page == 'news'"}class="active"{/if}
            onclick="MovimUtils.reload('{$c->route('news')}')"
            title="{$c->__('page.news')}"
        >
            <span class="primary icon">
                <i class="material-icons">receipt</i>
                <span data-key="news" class="counter"></span>
            </span>
        </li>
    {/if}
    <li {if="$page == 'contact'"}class="active"{/if}
        onclick="MovimUtils.reload('{$c->route('contact')}')"
        title="{$c->__('page.contacts')}"
    >
        <span class="primary icon">
            <i class="material-icons">people</i>
            <span data-key="invite" class="counter"></span>
        </span>
    </li>
    <li {if="$page == 'community'"}class="active"{/if}
        onclick="MovimUtils.reload('{$c->route('community')}')"
        title="{$c->__('page.communities')}"
    >
        <span class="primary icon"><i class="material-icons">group_work</i></span>
    </li>
    <li {if="$page == 'chat'"}class="active"{/if}
        onclick="MovimUtils.reload('{$c->route('chat')}')"
        title="{$c->__('page.chats')}"
    >
        <span class="primary icon">
            <i class="material-icons">forum</i>
            <span data-key="chat" class="counter"></span>
        </span>
    </li>
</ul>
