<div id="subscribe">
    <div class="placeholder">
        <i class="material-symbols">how_to_reg</i>
        <h1>{$c->__('subscribe.title')}</h1>
        <h4>{$c->__('subscribe.info')}</h4>
    </div>

    <ul class="list card active flex thick">
        {if="!empty($config->xmppdomain)"}
            <li
                class="block large color {$config->xmppdomain|stringToColor}"
                onclick="MovimUtils.redirect('{$c->route('accountnext', [$config->xmppdomain, false])}')">
                <i class="material-symbols main">person</i>
                <span class="primary icon bubble color transparent">
                    {$config->xmppdomain|firstLetterCapitalize}
                </span>
                <span class="control icon">
                    <i class="material-symbols">chevron_right</i>
                </span>
                <div>
                    <p class="normal">
                        {$config->xmppdomain}
                    </p>
                    {if="!empty($config->xmppdescription)"}
                    <p>
                        {$config->xmppdescription}<br />
                    </p>
                    {/if}
                </div>
            </li>
        {/if}
        {if="empty($config->xmppwhitelist) || in_array('movim.eu', $config->xmppwhitelist)"}
            <li
                class="block color large indigo"
                onclick="MovimUtils.redirect('{$c->route('accountnext', ['movim.eu', false])}')">
                <i class="material-symbols main">cloud</i>
                <span class="primary icon bubble">
                    <img src="theme/img/app/vectorial_square.svg">
                </span>
                <span class="control icon">
                    <i class="material-symbols">chevron_right</i>
                </span>
                <div>
                    <p>movim.eu</p>
                    <p>Official Movim XMPP Server<br /></p>
                </div>
            </li>
        {/if}
    </ul>
</div>
