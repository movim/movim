<section>
    <h3>{$c->__('post.preview')}</h3>
    <br />
    <article>
        <section>
            <content>
                {$content|prepareString}
            </content>
        </section>
    </article>
</section>
<div class="no_bar">
    <a onclick="Dialog.clear()" class="button flat">
        {$c->__('button.cancel')}
    </a>
</div>
