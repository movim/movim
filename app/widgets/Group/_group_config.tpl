<section class="scroll">
    <form name="config" data-sessionid="{$attributes->sessionid}" data-node="{$attributes->node}">
        {$form}
    </form>
</section>
<div>
    <a onclick="Dialog.clear()" class="button flat">
        {$c->__('button.close')}
    </a>
    <a onclick="Group_ajaxSetConfig(movim_parse_form('config'), '{$server}', '{$node}'); Dialog.clear();" class="button flat">
        {$c->__('button.save')}
    </a>
</div>
