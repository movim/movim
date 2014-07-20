{if="isset($pods)"}
    {loop="$pods->pods"}
    <article class="block">
        <header>             
            <span class="title">
                <a href="{$value->url}" target="_blank">{function="parse_url($value->url, PHP_URL_HOST)"}</a>
            </span>
        </header>
        <section class="content">{$value->description}</section>
        <footer class="padded">
            <img
            title="{$value->geo_country}" 
            alt="{$value->geo_country}" 
            src="{$c->flagPath($value->geo_country)}"/>
             • 
            <span>{$c->countryName($value->geo_country)}</span>
            {if="$value->geo_city != ''"}
                 • <span>{$value->geo_city}</span>
            {/if}
             • 
            <span>{$value->connected} • {$value->population}</span>
        </footer>
    </article>
    {/loop}
{else}
    <div class="padded">
        <div class="message error">{$c->__('api.error')}</div>
    </div>
{/if}
