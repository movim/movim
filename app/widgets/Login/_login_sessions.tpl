<section>
    <h3>{$c->__('account.title')}</h3>
    <br />
    <ul class="active">
        {loop="$sessions"}
        <li id="{$value->jid}" class="action condensed" title="{$value->jid}">
            <div class="action">
                <i class="zmdi zmdi-close"></i>
            </div>
            {$url = $value->getPhoto('s')}
            {if="$url"}
                <span class="icon bubble">
                    <img src="{$url}">
                </span>
            {else}
                <span class="icon bubble color {$value->jid|stringToColor}">
                    <i class="zmdi zmdi-account"></i>
                </span>
            {/if}
            <span>{$value->getTrueName()}</span>
            <p>{$value->jid}</p>
        </li>
        {/loop}
    </ul>
</section>
<div>
    <a class="button flat" href="{$c->route('about')}">
        <i class="zmdi zmdi-help"></i>
    </a>
    <a class="button flat" href="{$c->route('admin')}">
        <i class="zmdi zmdi-pages"></i>
    </a>
    <span class="button flat" onclick="Login.toForm()">{$c->__('form.another_account')}</span>
</div>
