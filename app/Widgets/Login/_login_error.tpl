<section>
    <ul class="list thick" id="login_error">
        <li>
            <span class="primary icon">
                <i class="material-symbols">error</i>
            </span>
            <div>
                <p>{$c->__('error.oops')}</p>
                <p>{$error}</p>
                {if="$errormessage"}
                    <p class="all">
                        <code>{$errormessage}</code>
                    </p>
                {/if}
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
