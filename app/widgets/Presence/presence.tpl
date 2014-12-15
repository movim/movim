<header
    id="presence_widget"
    class="big">
    <ul class="active divided">
        <li class="condensed">
            <div class="on_mobile control">
                <i onclick="movim_remove_class('body > nav', 'active')" class="md md-arrow-back"></i>
            </div>
            <span onclick="{$dialog}" class="icon bubble" style="background-image: url({$me->getPhoto('m')})"></span>
            <span onclick="{$dialog}">{$me->getTrueName()}</span>
            <p onclick="{$dialog}" class="wrap">{$presence->status}</p>
        </li>
    </ul>
</header>
