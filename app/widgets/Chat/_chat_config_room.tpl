<section class="scroll">
    <form name="config">
        {$form}
    </form>
</section>
<div>
    <a onclick="Dialog.clear()" class="button flat">
        {$c->__('button.close')}
    </a>
    <a onclick="Chat_ajaxSetRoomConfig(MovimUtils.parseForm('config'), '{$room}'); Dialog.clear();" class="button flat">
        {$c->__('button.save')}
    </a>
</div>
