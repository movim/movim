<section>
    <form name="changesubject">
        <h3>{$c->__('chatroom.subject')}</h3>

        <div>
            <textarea name="subject" placeholder="{$c->__('chatroom.subject')}">{$subject->subject}</textarea>
            <label for="subject">{$c->__('chatroom.subject')}</label>
        </div>
    </section>
    <div>
        <a class="button flat" onclick="Dialog.clear()">
            {$c->__('button.close')}
        </a>
        <a
            class="button flat"
            onclick="Chat_ajaxSetSubject('{$room}', movim_form_to_json('changesubject')); Dialog.clear();">
            {$c->__('button.save')}
        </a>
    </div>

</div>
