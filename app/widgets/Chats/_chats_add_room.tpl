<section>
    <form name="bookmarkmucadd">
        <h3>{$c->__('chats.add_room')}</h3>

        <div>
            <label>{$c->__('chatrooms.id')}</label>
            <input name="jid" placeholder="chatroom@server.com" type="email" required />
        </div>
        <div>
            <label>{$c->__('chatrooms.name')}</label>
            <input name="name" placeholder="{$c->__('chatrooms.name_placeholder')}" required />
        </div>
        <div>
            <label>{$c->__('chatrooms.nickname')}</label>
            <input name="nick" placeholder="{$me->getTrueName()}" value="{$me->getTrueName()}"/>
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
            onclick="Chats_ajaxChatroomAdd(movim_parse_form('bookmarkmucadd'));">
            {$c->__('button.add')}
        </a>
    </div>

</div>
