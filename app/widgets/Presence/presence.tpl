<ul class="list thick active" id="presence_widget" dir="ltr">
    <li>
        {$url = $me->getPhoto()}
        {if="$url"}
            <span class="primary icon bubble color status">
                <img src="{$url}">
            </span>
        {else}
            <span class="primary icon bubble color status">
                <i class="material-icons">person</i>
            </span>
        {/if}
        <div>
            <p class="line bold"><br /></p>
            <p class="line"><br /></p>
        </div>
    </li>
</ul>

<ul class="navigation list active" id="presence_widget_menu" dir="ltr">
    <li {if="$page == 'conf'"}class="active"{/if}
        title="{$c->__('page.configuration')}"
        onclick="MovimUtils.reload('{$c->route('conf')}')"
    >
        <span class="primary icon">
            <i class="material-icons">tune</i>
        </span>
        <div>
            <p class="normal line">{$c->__('page.configuration')}</p>
        </div>
    </li>
    {if="$c->getUser()->admin"}
        <li {if="$page == 'admin'"}class="active"{/if}
            onclick="MovimUtils.reload('{$c->route('admin')}')"
            title="{$c->__('page.configuration')}">
            <span class="primary icon">
                <i class="material-icons">manage_accounts</i>
            </span>
            <div>
                <p class="normal line">{$c->__('page.administration')}</p>
            </div>
        </li>
    {/if}

    <li {if="$page == 'help'"}class="active"{/if}
        onclick="MovimUtils.reload('{$c->route('help')}')"
        title="{$c->__('page.help')}"
    >
        <span class="primary icon">
            <i class="material-icons">help</i>
        </span>
        <div>
            <p class="normal line">{$c->__('page.help')}</p>
        </div>
    </li>
    <li class="on_desktop"
        onclick="Presence_ajaxAskLogout()"
        title="{$c->__('status.disconnect')}">
        <span class="primary icon"><i class="material-icons">exit_to_app</i></span>
        <div>
            <p class="normal line">{$c->__('status.disconnect')}</p>
        </div>
    </li>
    <hr />
</ul>
