<section>
    <form name="changesubject">
        <h3>{$c->__('chatroom.subject')}</h3>

        <div>
            <textarea name="subject" placeholder="{$c->__('chatroom.subject')}">{$subject->subject}</textarea>
            <label for="subject">{$c->__('chatroom.subject')}</label>
        </div>
    </section>
    <div>
        <button class="button flat" onclick="Dialog_ajaxClear()">
            {$c->__('button.close')}
        </button>
        <button
            class="button flat"
            onclick="Chat_ajaxSetSubject('{$room}', MovimUtils.formToJson('changesubject')); Dialog_ajaxClear();">
            {$c->__('button.save')}
        </button>
    </div>
</div>
