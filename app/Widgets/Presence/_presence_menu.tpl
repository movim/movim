<section>
    <header class="big color {$contact->color}"
        style="background-image: linear-gradient(to bottom, rgba(23,23,23,0.8) 0%, rgba(23,23,23,0.5) 100%), url('{$contact->getBanner(\Movim\ImageSize::XXL)}');"
        >
        <ul class="list thick">
            <li>
                <span class="primary icon bubble active status
                    {if="$presence->value != null"}{$presencetxt[$presence->value]}{/if}
                ">
                    <img src="{$contact->getPicture()}">
                </span>

                <span class="control icon active white divided" onclick="Presence_ajaxAskLogout(); Drawer.clear('menu')"
                    title="{$c->__('status.disconnect')}">
                    <i class="material-symbols">exit_to_app</i>
                </span>
                <div>
                    <p class="line">{$contact->truename}</p>
                    <p class="line">{$contact->id}</p>
                </div>
            </li>
        </ul>
    </header>

    <ul class="list active middle">
        <li title="{$c->__('status.visit_blog')}"
            onclick="MovimUtils.reload('{$c->route('contact', $c->me->id)}')"
        >
            <span class="primary icon">
                <i class="material-symbols">news</i>
            </span>
            <span class="control icon gray">
                <i class="material-symbols">chevron_right</i>
            </span>
            <div>
                <p class="line">{$c->__('status.visit_blog')}</p>
            </div>
        </li>
        {if="$c->me->hasPubsub()"}
            <li onclick="MovimUtils.reload('{$c->route('subscriptions')}')"
                title="{$c->__('communityaffiliation.subscriptions')}"
            >
                <span class="primary icon">
                    <i class="material-symbols">bookmarks</i>
                </span>
                <span class="control icon gray">
                    <i class="material-symbols">chevron_right</i>
                </span>
                <div>
                    <p>{$c->__('communityaffiliation.subscriptions')}</p>
                </div>
            </li>
        {/if}
    </ul>

    <hr />

    <ul class="list active">
        <li onclick="MovimUtils.reload('{$c->route('configuration')}')"
            title="{$c->__('page.configuration')}">
            <span class="primary icon">
                <i class="material-symbols">tune</i>
            </span>
            <span class="control icon gray">
                <i class="material-symbols">chevron_right</i>
            </span>
            <div>
                <p class="line">{$c->__('page.configuration')}</p>
            </div>
        </li>
        {if="$c->me->admin"}
            <li onclick="MovimUtils.reload('{$c->route('admin')}')"
                title="{$c->__('page.administration')}">
                <span class="primary icon">
                    <i class="material-symbols">manage_accounts</i>
                </span>
                <span class="control icon gray">
                    <i class="material-symbols">chevron_right</i>
                </span>
                <div>
                    <p class="line">{$c->__('page.administration')}</p>
                </div>
            </li>
        {/if}

        <li onclick="MovimUtils.reload('{$c->route('help')}')"
            title="{$c->__('page.help')}"
        >
            <span class="primary icon">
                <i class="material-symbols">help</i>
            </span>
            <span class="control icon gray">
                <i class="material-symbols">chevron_right</i>
            </span>
            <div>
                <p class="line">{$c->__('page.help')}</p>
            </div>
        </li>
    </ul>

</section>