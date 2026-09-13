<section id="publishpreview">
    {if="empty($title)"}
        <h3>{$c->__('publish.preview')}</h3>
    {else}
        <h3>{autoescape="off"}{$title|linkify}{/autoescape}</h3>
    {/if}
    <br />
    <article>
        <section>
            <div>
                {autoescape="off"}
                    {$content|linkify}
                {/autoescape}
            </div>
        </section>
    </article>
</section>
