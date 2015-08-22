<ul class="active">
    <a class="classic on_mobile" href="#">
        <li class="action" onclick="MovimTpl.toggleMenu()">
            <span class="icon bubble on_mobile"><i class="zmdi zmdi-menu"></i></span>
            <span>Movim</span>
        </li>
    </a>
    <a class="classic on_desktop" href="{$c->route('root')}">
        <li class="action {if="$page == 'main'"}active{/if}">
            <span class="icon bubble"><i class="zmdi zmdi-cloud-outline"></i></span>
            <span>{$c->__('page.home')}</span>
        </li>
    </a>
    <a class="classic on_mobile" href="{$c->route('root')}">
        <li class="action {if="$page == 'main'"}active{/if}">
            <span class="icon bubble"><i class="zmdi zmdi-home"></i></span>
            <span>{$c->__('page.home')}</span>
        </li>
    </a>
    <a class="classic {if="!$c->supported('pubsub')"}disabled{/if}" href="{$c->route('news')}">
        <li {if="$page == 'news'"}class="active"{/if}>
            <span class="icon"><i class="zmdi zmdi-receipt"></i></span>
            <span data-key="news" class="counter"></span>
            <span>{$c->__('page.news')}</span>
        </li>
    </a>
    <a class="classic" href="{$c->route('contact')}">
        <li {if="$page == 'contact'"}class="active"{/if}>
            <span class="icon"><i class="zmdi zmdi-accounts"></i></span>
            <span data-key="invite" class="counter"></span>
            <span>{$c->__('page.contacts')}</span>
        </li>
    </a>
    <a class="classic {if="!$c->supported('pubsub')"}disabled{/if}" href="{$c->route('group')}">
        <li {if="$page == 'group'"}class="active"{/if}>
            <span class="icon"><i class="zmdi zmdi-pages"></i></span>
            <span class="counter"></span>
            <span>{$c->__('page.groups')}</span>
        </li>
    </a>
    <a class="classic" href="{$c->route('chat')}">
        <li {if="$page == 'chat'"}class="active"{/if}>
            <span class="icon"><i class="zmdi zmdi-comments"></i></span>
            <span data-key="chat" class="counter"></span>
            <span>{$c->__('page.chats')}</span>
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
