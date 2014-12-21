<ul class="active">
    <li class="subheader">{$c->__('account.title')}</li>
{loop="$sessions"}
    <li onclick="chooseSession('{$value->jid}')">
        <div class="control">
            <i onclick="removeSession('{$value->jid}')" class="fa fa-times"></i>
        </div>
        <span class="icon bubble">
            <img src="{$value->getPhoto('s')}"/>
        </span>
        <span onclick="chooseSession('{$value->jid}')">{$value->getTrueName()}</span>
    </li>
{/loop}
    <li>
        <span class="icon bubble color green">
            <i class="md md-face-unlock"></i>
        </span>
        <span onclick="chooseSession('')">{$c->__('form.another_account')}</span>
    </li>
</ul>
