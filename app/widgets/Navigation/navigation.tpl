<ul class="list active">
    <a class="classic on_mobile" href="#">
        <li onclick="MovimTpl.toggleMenu()">
            <span class="primary icon bubble on_mobile"><i class="zmdi zmdi-menu"></i></span>
            <span class="control icon bubble"><i class="zmdi zmdi-arrow-back"></i></span>
            <p class="normal">Movim</p>
        </li>
    </a>
    <a class="classic on_desktop" href="{$c->route('root')}">
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
    </a>
    <a class="classic {if="!$c->supported('pubsub')"}disabled{/if}" href="{$c->route('news')}">
        <li {if="$page == 'news'"}class="active"{/if}>
            <span class="primary icon"><i class="zmdi zmdi-receipt"></i></span>
            <span data-key="news" class="counter"></span>
            <p class="normal">{$c->__('page.news')}</p>
        </li>
    </a>
    <a class="classic" href="{$c->route('contact')}">
        <li {if="$page == 'contact'"}class="active"{/if}>
            <span class="primary icon"><i class="zmdi zmdi-accounts"></i></span>
            <span data-key="invite" class="counter"></span>
            <p class="normal">{$c->__('page.contacts')}</p>
        </li>
    </a>
    <a class="classic {if="!$c->supported('pubsub')"}disabled{/if}" href="{$c->route('group')}">
        <li {if="$page == 'group'"}class="active"{/if}>
            <span class="primary icon"><i class="zmdi zmdi-pages"></i></span>
            <span class="counter"></span>
            <p class="normal">{$c->__('page.groups')}</p>
        </li>
    </a>
    <a class="classic" href="{$c->route('chat')}">
        <li {if="$page == 'chat'"}class="active"{/if}>
            <span class="primary icon"><i class="zmdi zmdi-comments"></i></span>
            <span data-key="chat" class="counter"></span>
            <p class="normal">{$c->__('page.chats')}</p>
        </li>
    </a>
    <!--
    <a class="classic" href="{$c->route('media')}">
        <li>
            <span class="icon"><i class="zmdi zmdi-photo"></i></span>
            <span class="counter"></span>
            <span>{$c->__('page.media')}</span>
        </li>
    </a>-->
</ul>

<ul class="list oppose active">
    <a class="classic {if="!$c->supported('pubsub')"}disabled{/if}" href="{$c->route('conf')}">
        <li>
            <span class="primary icon">
                <i class="zmdi zmdi-settings"></i>
            </span>
            <p class="normal">{$c->__('page.configuration')}</p>
        </li>
    </a>
    <a class="classic" href="{$c->route('help')}">
        <li>
            <span class="primary icon">
                <i class="zmdi zmdi-help"></i>
            </span>
            <p class="normal">{$c->__('page.help')}</p>
        </li>
    </a>
</ul>
