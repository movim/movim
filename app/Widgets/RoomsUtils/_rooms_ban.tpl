<section>
    <form name="bannedadd">
        <h3>{$c->__('room.banned_add')}</h3>

        <div>
            <input type="text" name="jid" placeholder="username@server.com"/>
            <label for="jid">{$c->__('input.username')}</label>
        </div>

        <div>
            <textarea name="reason" placeholder="{$c->__('room.reason')}" data-autoheight="true"></textarea>
            <label for="reason">{$c->__('room.reason')} ({$c->__('input.optional')})</label>
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
            onclick="RoomsUtils_ajaxAddBannedConfirm('{$room->conference}', MovimUtils.formToJson('bannedadd')); Dialog_ajaxClear();">
            {$c->__('button.submit')}
        </button>
    </div>
</div>
