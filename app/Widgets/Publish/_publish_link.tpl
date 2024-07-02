<section>
    <h3>{$c->__('publish.add_link')}</h3>
    <form name="link" onsubmit="return false;">
        <div>
            <input name="url"
                id="url"
                type="url"
                placeholder="http://myurl.com/picture.jpg"/>
            <label for="url">URL</label>
        </div>
    </form>
</section>
<div class="no_bar">
    <button onclick="Dialog_ajaxClear();" class="button flat">
        {$c->__('button.close')}
    </button>
    <button onclick="Publish.addUrl(); this.classList.add('disabled');" class="button flat">
        {$c->__('button.add')}
    </button>
</div>
