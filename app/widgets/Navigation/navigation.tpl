<ul class="active divided">
    <a class="classic" href="{$c->route('root')}">
        <li>
            <span class="icon"><i class="md md-speaker-notes"></i></span>
            <span class="counter"></span>
            <span>{$c->__('page.news')}</span>
        </li>
    </a>
    <a class="classic" href="{$c->route('contact')}">
        <li>
            <span class="icon"><i class="md md-people"></i></span>
            <span class="counter"></span>
            <span>{$c->__('page.contacts')}</span>
        </li>
    </a>
    <a class="classic" href="{$c->route('chat')}">
        <li>
            <span class="icon"><i class="md md-forum"></i></span>
            <span class="counter"></span>
            <span>{$c->__('page.chats')}</span>
        </li>
    </a>
    <a class="classic" href="{$c->route('media')}">
        <li>
            <span class="icon"><i class="md md-photo"></i></span>
            <span class="counter"></span>
            <span>{$c->__('page.media')}</span>
        </li>
    </a>
</ul>
<ul class="oppose active">
    <a class="classic" href="{$c->route('help')}">
        <li>
            <span class="icon">
                <i class="md md-help"></i>
            </span>
            <span>{$c->__('page.help')}</span>
        </li>
    </a>
    <a class="classic" href="{$c->route('conf')}">
        <li>
            <span class="icon">
                <i class="md md-settings"></i>
            </span>
            <span>{$c->__('page.configuration')}</span>
        </li>
    </a>
</ul>
