<section>
    <h3>{$c->__('account.title')}</h3>
    <br />
    <ul class="active">
        {loop="$sessions"}
        <li id="{$value->jid}" class="action">
            <div class="action">
                <i class="md md-close"></i>
            </div>
            {$url = $value->getPhoto('s')}
            {if="$url"}
                <span class="icon bubble">
                    <img src="{$url}">
                </span>
            {else}
                <span class="icon bubble color {$value->jid|stringToColor}">
                    <i class="md md-person"></i>
                </span>
            {/if}
            <span>{$value->getTrueName()}</span>
        </li>
        {/loop}
    </ul>
</section>
<div>
    <a class="button flat" href="{$c->route('admin')}">
        <i class="md md-pages"></i>
    </a>
    <span class="button flat" onclick="Login.toForm()">{$c->__('form.another_account')}</span>
</div>
