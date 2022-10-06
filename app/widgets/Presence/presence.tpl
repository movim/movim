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

<ul class="list active" id="presence_widget_menu" dir="ltr">
    <a href="{$c->route('conf')}"
        title="{$c->__('page.configuration')}">
         <li {if="$page == 'conf'"}class="active"{/if}>
             <span class="primary icon">
                 <i class="material-icons">tune</i>
             </span>
             <div>
                 <p class="normal">{$c->__('page.configuration')}</p>
             </div>
         </li>
    </a>
    {if="$c->getUser()->admin"}
        <a href="{$c->route('admin')}"
        title="{$c->__('page.configuration')}">
            <li {if="$page == 'admin'"}class="active"{/if}>
                <span class="primary icon">
                    <i class="material-icons">manage_accounts</i>
                </span>
                <div>
                    <p class="normal">{$c->__('page.administration')}</p>
                </div>
            </li>
        </a>
    {/if}
    <a href="{$c->route('help')}"
        title="{$c->__('page.help')}">
         <li {if="$page == 'help'"}class="active"{/if}>
             <span class="primary icon">
                 <i class="material-icons">help</i>
             </span>
             <div>
                 <p class="normal">{$c->__('page.help')}</p>
             </div>
         </li>
    </a>
    <li onclick="Presence_ajaxAskLogout()"
        title="{$c->__('status.disconnect')}">
        <span class="primary icon"><i class="material-icons">exit_to_app</i></span>
        <div>
            <p class="normal">{$c->__('status.disconnect')}</p>
        </div>
    </li>
    <hr />
</ul>
