<section class="scroll">
    <form name="config">
        {autoescape="off"}
            {$form}
        {/autoescape}
    </form>
</section>
<div>
    <a onclick="Dialog_ajaxClear()" class="button flat">
        {$c->__('button.close')}
    </a>
    <a onclick="Chat_ajaxSetRoomConfig(MovimUtils.formToJson('config'), '{$room}'); Dialog_ajaxClear();" class="button flat">
        {$c->__('button.save')}
    </a>
</div>
