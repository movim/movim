{if="isset($pods)"}
    {loop="$pods->pods"}
    <article class="block">
        <header>             
            <span class="title">
                <a href="{$value->url}" target="_blank">{function="parse_url($value->url, PHP_URL_HOST)"}</a>
            </span>
        </header>
        <section class="content">{$value->description}</section>
        <footer>
            <img
            title="{$value->language}" 
            alt="{$value->language}" 
            src="{$c->flagPath($value->language)}"/>
            <span>{$value->connected} • {$value->population}</span>
        </footer>
    </article>
    {/loop}
{else}
    <div class="padded">
        <div class="message error">{$c->__('api.error')}</div>
    </div>
{/if}
