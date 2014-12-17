<ul class="active divided">
    <li>
        <div class="on_mobile control">
            <i onclick="movim_remove_class('body > nav', 'active')" class="md md-arrow-back"></i>
        </div>
        <span class="icon bubble"><i class="md md-camera-roll"></i></span>
        Movim
    </li>
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
    <!--
    <a class="classic" href="{$c->route('media')}">
        <li>
            <span class="icon"><i class="md md-photo"></i></span>
            <span class="counter"></span>
            <span>{$c->__('page.media')}</span>
        </li>
    </a>-->
</ul>
