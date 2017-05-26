<section>
    <form name="invite">
        <h3>{$c->__('room.invite')}</h3>
        <h4>{$room}</h4>
        <div>
            <input
                readonly
                value="{$c->route('login', $invite->code)}">
            <label>{$c->__('room.invite_code')}</label>
        </div>
        <h2 style="text-align: center;">{$c->__('global.or')}</h2>
        <div>
            <input type="hidden" value="{$room}" name="to" id="to"/>
            <datalist id="contact_list" style="display: none;">
                {if="is_array($contacts)"}
                    {loop="$contacts"}
                        <option value="{$value->jid}"/>
                    {/loop}
                {/if}
            </datalist>
            <input
                name="invite"
                list="contact_list"
                id="invite"
                type="email"
                required
                placeholder="user@server.tld"/>
            <label>{$c->__('roster.add_contact_info1')}</label>
        </div>
    </section>
    <div>
        <button class="button flat" onclick="Dialog_ajaxClear()">
            {$c->__('button.cancel')}
        </button>
        <button
            class="button flat"
            onclick="Rooms_ajaxInvite(MovimUtils.formToJson('invite'));">
            {$c->__('button.invite')}
        </button>
    </div>
</div>
