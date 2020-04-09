<section>
    <h3>{$c->__('publishbrief.preview')}</h3>
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
<div class="no_bar">
    <button onclick="Dialog_ajaxClear()" class="button flat">
        {$c->__('button.close')}
    </button>
</div>
