<section>
    <h3>{$c->__('publish.preview')}</h3>
    <br />
    <article>
        <section>
            <content>
                {$content|addHashtagsLinks}
            </content>
        </section>
    </article>
</section>
<div class="no_bar">
    <button onclick="Dialog_ajaxClear()" class="button flat">
        {$c->__('button.close')}
    </button>
</div>
