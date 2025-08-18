<section>
    <h3>{$c->__('account.delete_bundle_title')}</h3>
    <br />

    <h4 class="gray">{$c->__('account.delete_bundle_text')}</h4>
    <br />

    <ul class="list">
        <li>
            <span class="primary icon red">
                <i class="material-symbols">fingerprint</i>
            </span>
            <div>
                <p class="normal">
                    <span class="fingerprint">
                        {$fingerprint->fingerprint}
                    </span>
                </p>
            </div>
        </li>
    </ul>
</section>
<footer>
    <button onclick="Dialog_ajaxClear()" class="button flat">
        {$c->__('button.cancel')}
    </button>
    <button
        name="submit"
        class="button flat color red"
        onclick="Account.deleteBundleConfirm({$fingerprint->bundleid}); Dialog_ajaxClear()">
        {$c->__('button.destroy')}
    </button>
</footer>
