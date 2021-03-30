<section id="publishpreview">
    {if="empty($title)"}
        <h3>{$c->__('publish.preview')}</h3>
    {else}
        <h3>{autoescape="off"}{$title|addHashtagsLinks}{/autoescape}</h3>
    {/if}
    <br />
    <article>
        <section>
            <div>
                {autoescape="off"}
                    {$content|addHashtagsLinks}
                {/autoescape}
            </div>
        </section>
    </article>
</section>
<div>

</div>
