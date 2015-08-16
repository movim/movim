<section>
    <form name="bookmarkmucadd">
        <h3>{$c->__('rooms.add')}</h3>

        <div>
            <input name="jid" placeholder="chatroom@server.com" type="email" required />
            <label>{$c->__('chatrooms.id')}</label>
        </div>
        <div>
            <input name="name" placeholder="{$c->__('chatrooms.name_placeholder')}" required />
            <label>{$c->__('chatrooms.name')}</label>
        </div>
        <div>
            <input name="nick" placeholder="{$username}" value="{$username}"/>
            <label>{$c->__('chatrooms.nickname')}</label>
        </div>
        <!--
        <div class="element large mini">
            <label>{$c->__('chatroom.autojoin_label')}</label>
            <div class="checkbox">
                <input type="checkbox" id="autojoin" name="autojoin"/>
                <label for="autojoin"></label>
            </div>
        </div>
        -->
    </section>
    <div>
        <a class="button flat" onclick="Dialog.clear()">
            {$c->__('button.close')}
        </a>
        <a
            class="button flat"
            onclick="Rooms_ajaxChatroomAdd(movim_parse_form('bookmarkmucadd'));">
            {$c->__('button.add')}
        </a>
    </div>

</div>
