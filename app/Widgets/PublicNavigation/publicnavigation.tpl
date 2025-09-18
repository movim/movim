<ul id="publicnavigation" class="list thick">
    <li>
        <span class="primary icon">
            <a href="{$c->route('main')}">
                <img src="/theme/img/app/vectorial.svg">
            </a>
        </span>
        <div>
            <p>
                {$app_title}
                {if="!$c->me->session"}
                    <a class="button oppose color" title="{$c->__('button.register')}" href="{$c->route('account')}">
                        {$c->__('button.register')}
                    </a>
                    <a class="button oppose flat" title="{$c->__('page.login')}" href="{$c->route('main')}">
                        {$c->__('page.login')}
                    </a>
                {/if}
            </p>
            <p>{$base_host}</p>
        </div>
    </li>
</ul>
