<header class="big top color" style="background-image: url({$me->getBanner()})">
    <ul class="list thick">
        <li class="block">
            <span
                class="primary icon bubble color {$me->color}"
                style="background-image: url({$me->getPicture()})">
            </span>
            <div>
                <a class="button color transparent active" onclick="Avatar_ajaxGetForm()" title="{$c->__('avatar.change')}">
                    <i class="material-symbols">person_edit</i>
                </a>

                <a class="button color transparent oppose active" onclick="Avatar_ajaxGetBannerForm()" title="{$c->__('banner.change')}">
                    <i class="material-symbols">landscape_2</i>
                </a>
            </div>
        </li>
    </ul>
    <br />
</header>
