<div id="subscribe">
    <ul class="list thick">
        <li>
            <div>
                <p>{$c->__('subscribe.title')}</p>
                <p>{$c->__('subscribe.info')}</p>
            </div>
        </li>
    </ul>

    <ul class="list card active flex thick">
        {if="!empty($config->xmppdomain)"}
            <li
                class="block large"
                onclick="MovimUtils.redirect('{$c->route('accountnext', [$config->xmppdomain, false])}')">
                <span class="primary icon bubble color {$config->xmppdomain|stringToColor}">
                    {$config->xmppdomain|firstLetterCapitalize}
                </span>
                <div>
                    <p>
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
        <li
            class="block"
            onclick="MovimUtils.redirect('{$c->route('accountnext', ['movim.eu', false])}')">
            <span class="primary icon bubble">
                <img src="theme/img/app/vectorial_square.svg">
            </span>
            <div>
                <p>movim.eu</p>
                <p>Official Movim XMPP Server<br /></p>
            </div>
        </li>
    </ul>
    <ul class="list thick">
        <li class="block">
            <div>
                <p></p>
                <p>{$c->__('subscribe.server_question')}</p>
                <p>
                    {$c->__('subscribe.server_contact')} • <a href="https://movim.eu/">https://movim.eu/</a>
                </p>
            </div>
        </li>
    </ul>
</div>
