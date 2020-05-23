<section>
    <form name="changesubject">
        <h3>{$c->__('chatroom.subject')}</h3>

        <div>
            <textarea name="subject" placeholder="{$c->__('chatroom.subject')}" data-autoheight="true">{$room->subject}</textarea>
            <label for="subject">{$c->__('chatroom.subject')}</label>
        </div>
    </form>
</section>
<div>
    <div>
        <button class="button flat" onclick="Dialog_ajaxClear()">
            {$c->__('button.close')}
        </button>
        <button
            class="button flat"
            onclick="RoomsUtils_ajaxSetSubject('{$room->conference}', MovimUtils.formToJson('changesubject')); Dialog_ajaxClear();">
            {$c->__('button.save')}
        </button>
    </div>
</div>
