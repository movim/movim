<ul class="list thick flex">
    <li class="block active" onclick="Avatar_ajaxGetForm()">
        {$url = $me->getPhoto()}
        {if="$url"}
            <span
                class="primary icon bubble"
                style="background-image: url({$url})">
            </span>
        {else}
            <span
                class="primary icon bubble color {$me->jid|stringToColor}">
                <i class="material-icons">person</i>
            </span>
        {/if}
        <span class="control icon gray">
            <i class="material-icons">chevron_right</i>
        </span>
        <div>
            <p>{$c->__('avatar.change')}</p>
            {if="$url"}
                <p>{$c->__('avatar.upload_new')}</p>
            {else}
                <p>{$c->__('avatar.missing')}</p>
            {/if}
        </div>
    </li>
    <li class="block active" onclick="Avatar_ajaxGetBannerForm()">
        {$url = $me->getBanner()}
        {if="$url"}
            <span
                class="primary icon bubble"
                style="background-image: url({$url})">
            </span>
        {else}
            <span
                class="primary icon bubble color {$me->jid|stringToColor}">
                <i class="material-icons">person</i>
            </span>
        {/if}
        <span class="control icon gray">
            <i class="material-icons">chevron_right</i>
        </span>
        <div>
            <p>{$c->__('banner.change')}</p>
            {if="$url"}
                <p>{$c->__('avatar.upload_new')}</p>
            {else}
                <p>{$c->__('banner.missing')}</p>
            {/if}
        </div>
    </li>
</ul>