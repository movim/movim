{if="isset($pods)"}
    <ul class="flex simple thick card">
        {loop="$pods->pods"}
        <li class="block condensed">
            <span class="info">{$value->connected} / {$value->population}</span>
            <span>
                <a href="{$value->url}" target="_blank">
                    {function="parse_url($value->url, PHP_URL_HOST)"}
                </a>
            </span>
            <p>{$value->description}</p>
            <span class="info">
                {$value->version}
            </span>
            <p>
                <img
                title="{$value->geo_country}" 
                alt="{$value->geo_country}" 
                src="{$c->flagPath($value->geo_country)}"/>
                <span>{$c->countryName($value->geo_country)}</span>
                {if="$value->geo_city != ''"}
                     • <span>{$value->geo_city}</span>
                {/if}
            </p>
        </li>
        {/loop}
    </ul>
{else}
    <div class="padded">
        <div class="message error">{$c->__('api.error')}</div>
    </div>
{/if}
