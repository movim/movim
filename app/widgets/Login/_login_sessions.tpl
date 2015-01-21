<section>
    <h3>{$c->__('account.title')}</h3>
    <br />
    <ul class="active">
        {loop="$sessions"}
        <li id="{$value->jid}" class="action">
            <div class="action">
                <i class="fa fa-times"></i>
            </div>
            <span class="icon bubble">
                <img src="{$value->getPhoto('s')}"/>
            </span>
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
