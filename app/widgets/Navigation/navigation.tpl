<ul class="list active">
    <a class="classic on_mobile" href="#">
        <li onclick="MovimTpl.toggleMenu()">
            <span class="primary icon bubble on_mobile"><i class="zmdi zmdi-menu"></i></span>
            <span class="control icon bubble"><i class="zmdi zmdi-arrow-back"></i></span>
            <p class="normal">Movim</p>
        </li>
    </a>
    <!--<a class="classic on_desktop" href="{$c->route('root')}">
        <li class="{if="$page == 'main'"}active{/if}">
            <span class="primary icon bubble"><i class="zmdi zmdi-cloud-outline"></i></span>
            <p class="normal">{$c->__('page.home')}</p>
        </li>
    </a>
    <a class="classic on_mobile" href="{$c->route('root')}">
        <li class="{if="$page == 'main'"}active{/if}">
            <span class="primary icon bubble"><i class="zmdi zmdi-home"></i></span>
            <p class="normal">{$c->__('page.home')}</p>
        </li>
    </a>-->
    <a class="classic {if="!$c->supported('pubsub')"}disabled{/if}"
       href="{$c->route('news')}"
       title="{$c->__('page.news')}">
        <li {if="$page == 'news'"}class="active"{/if}>
            <span class="primary icon"><i class="zmdi zmdi-receipt"></i></span>
            <span data-key="news" class="counter"></span>
            <p class="normal">{$c->__('page.news')}</p>
        </li>
    </a>
    <a class="classic" href="{$c->route('contact')}"
       title="{$c->__('page.contacts')}">
        <li {if="$page == 'contact'"}class="active"{/if}>
            <span class="primary icon"><i class="zmdi zmdi-accounts"></i></span>
            <span data-key="invite" class="counter"></span>
            <p class="normal">{$c->__('page.contacts')}</p>
        </li>
    </a>
    <a class="classic {if="!$c->supported('pubsub')"}disabled{/if}"
       href="{$c->route('group')}"
       title="{$c->__('page.groups')}">
        <li {if="$page == 'group'"}class="active"{/if}>
            <span class="primary icon"><i class="zmdi zmdi-pages"></i></span>
            <span class="counter"></span>
            <p class="normal">{$c->__('page.groups')}</p>
        </li>
    </a>
    <a class="classic" href="{$c->route('chat')}"
       title="{$c->__('page.chats')}">
        <li {if="$page == 'chat'"}class="active"{/if}>
            <span class="primary icon"><i class="zmdi zmdi-comments"></i></span>
            <span data-key="chat" class="counter"></span>
            <p class="normal">{$c->__('page.chats')}</p>
        </li>
    </a>
</ul>

<ul class="list divided oppose active">
    <li onclick="Search_ajaxRequest()">
        <span class="primary icon">
            <i class="zmdi zmdi-search"></i>
        </span>
        <p class="normal">{$c->__('button.search')}</p>
    </li>
    <a class="classic {if="!$c->supported('pubsub')"}disabled{/if}"
       href="{$c->route('conf')}"
       title="{$c->__('page.configuration')}">
        <li>
            <span class="primary icon">
                <i class="zmdi zmdi-settings"></i>
            </span>
            <p class="normal">{$c->__('page.configuration')}</p>
        </li>
    </a>
    <a class="classic on_desktop"
       href="{$c->route('help')}"
       title="{$c->__('page.help')}">
        <li>
            <span class="primary icon">
                <i class="zmdi zmdi-help"></i>
            </span>
            <p class="normal">{$c->__('page.help')}</p>
        </li>
    </a>
    <li onclick="Presence_ajaxLogout()"
        title="{$c->__('status.disconnect')}">
        <span class="primary icon"><i class="zmdi zmdi-sign-in"></i></span>
        <p class="normal">{$c->__('status.disconnect')}</p>
    </li>
</ul>
