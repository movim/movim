<section>
    <h3>{$c->__('publishbrief.add_link')}</h3>
    <form name="link">
        <div>
            <input name="url"
                id="url"
                type="url"
                placeholder="http://myurl.com/picture.jpg"/>
            <label for="url">URL</label>
        </div>
    </form>
</section>
<div>
    <button onclick="Dialog_ajaxClear();" class="button flat">
        {$c->__('button.close')}
    </button>
    <button onclick="PublishBrief.addUrl(); this.classList.add('disabled')" class="button flat">
        {$c->__('button.add')}
    </button>
</div>
