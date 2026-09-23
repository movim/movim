<section>
    <form name="changesubject">
        <h3>{$c->__('chatrooms.subject')}</h3>

        <div>
            <textarea name="subject" placeholder="{$c->__('chatrooms.subject')}" oninput="MovimUtils.textareaAutoheight(this);">{if="$room->subject"}{$room->subject}{/if}</textarea>
            <label for="subject">{$c->__('chatrooms.subject')}</label>
        </div>
    </form>
</section>
<footer>
    <button class="button flat" onclick="Dialog_ajaxClear()">
        {$c->__('button.close')}
    </button>
    <button
        class="button flat"
        onclick="RoomsUtils_ajaxSetSubject('{$room->conference}', MovimUtils.formToJson('changesubject')); Dialog_ajaxClear();">
        {$c->__('button.save')}
    </button>
</footer>
