<section>
    <h3>{$c->__('account.title')}</h3>
    <br />
    <ul class="active">
        {loop="$sessions"}
        <li id="{$value->jid}" class="action">
            <div class="action">
                <i onclick="Login.removeSession('{$value->jid}')" class="fa fa-times"></i>
            </div>
            <span onclick="Login.choose('{$value->jid}')" class="icon bubble">
                <img src="{$value->getPhoto('s')}"/>
            </span>
            <span onclick="Login.choose('{$value->jid}')">{$value->getTrueName()}</span>
        </li>
        {/loop}
    </ul>
</section>
<div>
    <a class="button flat" href="{$c->route('admin')}">
        <i class="md md-pages"></i>
    </a>
    <span class="button flat" onclick="Login.choose('')">{$c->__('form.another_account')}</span>
</div>
