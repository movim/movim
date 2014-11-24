<ul>
{loop="$sessions"}
    <li>
        <img onclick="chooseSession('{$value->jid}')" src="{$value->getPhoto('m')}"/>
        <span onclick="chooseSession('{$value->jid}')">{$value->getTrueName()}</span>
        <a class="button oppose color alone transparent" onclick="removeSession('{$value->jid}')"><i class="fa fa-times"></i></a>
    </li>
{/loop}

    <li>
        <img onclick="chooseSession('')" src="{$empty->getPhoto('m')}"/>
        <span onclick="chooseSession('')">{$c->__('form.another_account')}</span>
    </li>
</ul>
