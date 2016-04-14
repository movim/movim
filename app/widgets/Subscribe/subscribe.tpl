<div id="subscribe">
    <ul class="list thick">
        <li>
            <p>{$c->__('subscribe.title')}</p>
            <p>{$c->__('subscribe.info')}</p>
        </li>
    </ul>

    <ul class="list card shadow active flex">
        {if="!empty($config->xmppdomain)"}
            <li
                class="block large"
                onclick="movim_redirect('{$c->route('accountnext', array($config->xmppdomain, false))}')">
                <span class="primary icon bubble color {$config->xmppdomain|stringToColor}">
                    {$config->xmppdomain|firstLetterCapitalize}
                </span>
                <p>
                    {if="!empty($config->xmppcountry)"}
                        <span class="info">
                            <img
                                class="flag"
                                title="{$config->xmppcountry}"
                                alt="{$config->xmppc}"
                                src="{$c->flagPath($config->xmppcountry)}"/>
                        </span>
                    {/if}
                    {$config->xmppdomain}
                </p>
                {if="!empty($config->xmppdescription)"}
                <p>
                    {$config->xmppdescription}<br />
                </p>
                {/if}
            </li>
        {/if}
        {loop="$servers"}
        <li
            class="block"
            onclick="movim_redirect('{$c->route('accountnext', array($value->domain, false))}')">
            <span class="primary icon bubble color {$value->description|stringToColor}">
                {if="isset($value->checked) && $value->checked"}
                    <i class="fa md-star-outline"></i>
                {else}
                    {$value->domain|firstLetterCapitalize}
                {/if}
            </span>
            <p>
                <span class="info">
                <img
                    class="flag"
                    title="{$value->geo_country}"
                    alt="{$value->geo_country}"
                    src="{$c->flagPath($value->geo_country)}"/>
                </span>
                {$value->domain}
            </p>
            <p>
                {$value->description}<br />
            </p>
        </li>
        {/loop}

        <li class="block">
            <span class="primary icon bubble color orange">
                <i class="zmdi zmdi-globe-alt"></i>
            </span>
            <p>{$c->__('subscribe.server_question')}</p>
            <p>
                {$c->__('subscribe.server_contact')} • <a href="https://movim.eu/">https://movim.eu/</a>
            </p>
        </li>
    </ul>
</div>
