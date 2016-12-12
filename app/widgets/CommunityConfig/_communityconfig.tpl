<section class="scroll">
    <form name="config" data-sessionid="{$attributes->sessionid}" data-node="{$attributes->node}">
        {$form}
    </form>
</section>
<div>
    <a onclick="Dialog_ajaxClear()" class="button flat">
        {$c->__('button.close')}
    </a>
    <a onclick="CommunityConfig_ajaxSetConfig(MovimUtils.parseForm('config'), '{$server|echapJS}', '{$node|echapJS}'); Dialog_ajaxClear();"
       class="button flat">
        {$c->__('button.save')}
    </a>
</div>
