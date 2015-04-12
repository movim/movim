<ul class="active">
    <a class="classic" href="{$c->route('root')}">
        <li class="action {if="$page == 'main'"}active{/if}">
            <!--<div class="action on_mobile">
                <i onclick="MovimTpl.toggleMenu()" class="md md-arrow-back"></i>
            </div>-->
            <span class="icon bubble"><i class="md md-cloud-queue"></i></span>
            <span>Movim</span>
        </li>
    </a>
    <a class="classic {if="!$c->supported('pubsub')"}disabled{/if}" href="{$c->route('news')}">
        <li {if="$page == 'news'"}class="active"{/if}>
            <span class="icon"><i class="md md-view-list"></i></span>
            <span data-key="news" class="counter"></span>
            <span>{$c->__('page.news')}</span>
        </li>
    </a>
    <a class="classic" href="{$c->route('contact')}">
        <li {if="$page == 'contact'"}class="active"{/if}>
            <span class="icon"><i class="md md-people"></i></span>
            <span class="counter"></span>
            <span>{$c->__('page.contacts')}</span>
        </li>
    </a>
    <a class="classic {if="!$c->supported('pubsub')"}disabled{/if}" href="{$c->route('group')}">
        <li {if="$page == 'group'"}class="active"{/if}>
            <span class="icon"><i class="md md-pages"></i></span>
            <span class="counter"></span>
            <span>{$c->__('page.groups')}</span>
        </li>
    </a>
    <a class="classic" href="{$c->route('chat')}">
        <li {if="$page == 'chat'"}class="active"{/if}>
            <span class="icon"><i class="md md-forum"></i></span>
            <span data-key="chat" class="counter"></span>
            <span>{$c->__('page.chats')}</span>
        </li>
    </a>
    <!--
    <a class="classic" href="{$c->route('media')}">
        <li>
            <span class="icon"><i class="md md-photo"></i></span>
            <span class="counter"></span>
            <span>{$c->__('page.media')}</span>
        </li>
    </a>-->
</ul>
