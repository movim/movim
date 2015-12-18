<section>
    <h3>{$c->__('account.title')}</h3>
    <br />
    <ul class="list active middle">
        {loop="$sessions"}
        <li id="{$value->jid}" title="{$value->jid}">
            {$url = $value->getPhoto('s')}
            {if="$url"}
                <span class="primary icon bubble">
                    <img src="{$url}">
                </span>
            {else}
                <span class="primary icon bubble color {$value->jid|stringToColor}">
                    <i class="zmdi zmdi-account"></i>
                </span>
            {/if}
            <span class="control icon gray">
                <i class="zmdi zmdi-close"></i>
            </span>
            <p class="line">{$value->getTrueName()}</p>
            <p class="line">{$value->jid}</p>
        </li>
        {/loop}
    </ul>
</section>
<div>
    <a class="button flat" href="{$c->route('about')}" title="{$c->__('page.about')}">
        <i class="zmdi zmdi-help"></i>
    </a>
    <a class="button flat" href="{$c->route('admin')}" title="{$c->__('page.administration')}">
        <i class="zmdi zmdi-pages"></i>
    </a>
    <span class="button flat" onclick="Login.toForm()">{$c->__('form.another_account')}</span>
</div>
