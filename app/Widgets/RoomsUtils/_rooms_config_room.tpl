<section class="scroll">
    <form name="config">
        {autoescape="off"}
            {$form}
        {/autoescape}
    </form>
</section>
<footer>
    <a onclick="Dialog_ajaxClear()" class="button flat">
        {$c->__('button.close')}
    </a>
    <a onclick="RoomsUtils_ajaxSetConfig(MovimUtils.formToJson('config'), '{$room}'); Dialog_ajaxClear();" class="button flat">
        {$c->__('button.save')}
    </a>
</footer>
