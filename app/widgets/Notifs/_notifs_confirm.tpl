<section>
    <h3>{$c->__('notifs.manage')}</h3>
    <br />
    <h4 class="gray">{$c->__('wants_to_talk', $jid)}</h4>
    <ul class="active">
        <li onclick="Notifs_ajaxAccept('{$jid|echapJS}'); Dialog.clear();">
            <span class="icon green">
                <i class="md md-person-add"></i>
            </span>
            {$c->__('button.accept')}
        </li>
        <li onclick="Notifs_ajaxRefuse('{$jid|echapJS}'); Dialog.clear();">
            <span class="icon red">
                <i class="md md-close"></i>
            </span>
            {$c->__('button.refuse')}
        </li>
    </ul>

</section>
<div>
    <a onclick="Dialog.clear()" class="button flat">
        {$c->__('button.close')}
    </a>
</div>

