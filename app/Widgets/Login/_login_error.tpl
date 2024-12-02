<section>
    <ul class="list thick">
        <li>
            <span class="primary icon">
                <i class="material-symbols">error</i>
            </span>
            <div>
                <p>{$c->__('error.oops')}</p>
                <p>{$error}</p>
            </div>
        </li>
    </ul>
    <ul class="list">
        <li>
            <div>
                <p class="center">
                    <span class="button flat" onclick="MovimUtils.redirect('{$c->route('disconnect')}');">
                        {$c->__('button.return')}
                    </span>
                </p>
            </div>
        </li>
    </ul>
</section>
