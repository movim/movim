<section>
    <h3>{$c->__('notifs.manage')}</h3>
    <br />
    <h4 class="gray">{$c->__('notifs.wants_to_talk', $jid)}</h4>
    <ul class="list active">
        <li onclick="Notifs_ajaxAccept('{$jid|echapJS}'); Dialog_ajaxClear();">
            <span class="icon control green">
                <i class="zmdi zmdi-account-add"></i>
            </span>
            <p class="normal">{$c->__('button.accept')}</p>
        </li>
        <li onclick="Notifs_ajaxRefuse('{$jid|echapJS}'); Dialog_ajaxClear();">
            <span class="control icon red">
                <i class="zmdi zmdi-close"></i>
            </span>
            <p class="normal">{$c->__('button.refuse')}</p>
        </li>
    </ul>

</section>
<div>
    <a onclick="Dialog_ajaxClear()" class="button flat">
        {$c->__('button.close')}
    </a>
</div>
