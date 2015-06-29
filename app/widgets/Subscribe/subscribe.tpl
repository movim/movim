<div id="subscribe">
    <ul class="simple thick">
        <li class="condensed">
            <span>{$c->__('subscribe.title')}</span>
            <p>{$c->__('subscribe.info')}</p>
        </li>
    </ul>

    <ul class="thick active flex card">
        {loop="$servers"}
        <li
            class="block condensed"
            onclick="movim_redirect('{$c->route('accountnext', array($value->domain, false))}')">
            <span class="icon bubble color {$value->description|stringToColor}">
                {if="isset($value->checked) && $value->checked"}
                    <i class="fa md-star-outline"></i>
                {else}
                    {$value->domain|firstLetterCapitalize}
                {/if}
            </span>
            <span class="info">
            <img
                class="flag"
                title="{$value->geo_country}"
                alt="{$value->geo_country}"
                src="{$c->flagPath($value->geo_country)}"/>
            </span>
            <span>{$value->domain}</span>
            <p>
                {$value->description}<br />
                <a target="_blank" href="{$value->url}">
                    {$value->url}
                </a>
            </p>
        </li>
        {/loop}

        <li class="block condensed">
            <span class="icon bubble color orange">
                <i class="zmdi zmdi-globe-alt"></i>
            </span>
            <span>{$c->__('subscribe.server_question')}</span>
            <p>
                {$c->__('subscribe.server_contact')} • <a href="https://movim.eu/">https://movim.eu/</a>
            </p>
        </li>
    </ul>
</div>
