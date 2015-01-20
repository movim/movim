<li onclick="{$dialog}" class="condensed action">
    <div>
        <i class="md md-edit"></i>
    </div>
    <span class="icon bubble" style="background-image: url({$me->getPhoto('m')})"></span>
    <span>{$me->getTrueName()}</span>
    <p class="wrap">{$presence->status}</p>
</li>
<a class="block classic" href="{$c->route('conf')}">
    <li>
        <span class="icon">
            <i class="md md-settings"></i>
        </span>
        <span>{$c->__('page.configuration')}</span>
    </li>
</a>
<a class="block classic" href="{$c->route('help')}">
    <li>
        <span class="icon">
            <i class="md md-help"></i>
        </span>
        <span>{$c->__('page.help')}</span>
    </li>
</a>
