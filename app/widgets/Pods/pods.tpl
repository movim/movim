{loop="$pods"}
<article class="block">
    <header>             
        <span class="title">
            <a href="{$value->url}" target="_blank">{$value->url}</a>
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
