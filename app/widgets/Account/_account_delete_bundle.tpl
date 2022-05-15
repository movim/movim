<section>
    <h3>{$c->__('account.delete_bundle_title')}</h3>
    <br />

    <h4 class="gray">{$c->__('account.delete_bundle_text')}</h4>
    <br />

    <p class="normal">
        <span class="fingerprint">
            {$bundle->fingerprint}
        </span>
    </p>
</ul>

</section>
<div class="no_bar">
    <button onclick="Dialog_ajaxClear()" class="button flat">
        {$c->__('button.cancel')}
    </button>
    <button
        name="submit"
        class="button flat color red"
        onclick="Account_ajaxDeleteBundle({$bundle->bundleid}); Dialog_ajaxClear()">
        {$c->__('button.destroy')}
    </button>
</div>