<section>
    <form name="bookmarkmucadd">
        {if="$create"}
            <h3>{$c->__('rooms.create')}</h3>
        {else}
            {if="isset($conference)"}
                <h3>{$c->__('rooms.edit')}</h3>
            {else}
                <h3>{$c->__('rooms.join')}</h3>
            {/if}
        {/if}

        {if="isset($id)"}
            <h4 class="line">{$id}</h4>
        {/if}

        {if="!$create"}
            {if="$gateways->isNotEmpty() && !isset($conference)"}
                <div>
                    <div class="select">
                        <select onchange="RoomsUtils_ajaxDiscoGateway(this.value)">
                            <option value="">{$c->__('rooms.default_room')}</option>
                            {loop="$gateways"}
                                <option value="{$value->server}">
                                    {$value->name}
                                    {if="$value->identities()->first()"}
                                        ({$value->identities()->first()->type})
                                    {/if}
                                </option>
                            {/loop}
                        </select>
                    </div>
                    <label>{$c->__('rooms.type_room')}</label>
                </div>
            {/if}

            <div id="gateway_rooms"></div>

            <datalist id="suggestions">
            </datalist>
        {/if}

        {if="$create"}
            <div>
                <ul class="list middle fill">
                    <li>
                        <span class="primary icon gray">
                            <i class="material-icons">people_alt</i>
                        </span>
                        <span class="control">
                            <div class="radio">
                                <input name="type" value="groupchat"
                                    id="type_groupchat" type="radio"
                                    checked>
                                <label for="type_groupchat"></label>
                            </div>
                        </span>
                        <div>
                            <p>{$c->__('room.group_chat')}</p>
                            <p>{$c->__('room.group_chat_text')}</p>
                        </div>
                    </li>
                    <li>
                        <span class="primary icon gray">
                            <i class="material-icons">wifi_tethering</i>
                        </span>
                        <span class="control">
                            <div class="radio">
                                <input name="type" value="channel"
                                    id="type_channel" type="radio">
                                <label for="type_channel"></label>
                            </div>
                        </span>
                        <div>
                            <p>{$c->__('room.channel')}</p>
                            <p>{$c->__('room.channel_text')}</p>
                        </div>
                    </li>
                </ul>
            </div>
            <div>
                <input
                    {if="isset($conference)"}
                        value="{$conference->name}"
                    {elseif="isset($info)"}
                        value="{$info->name}"
                    {elseif="isset($name)"}
                        value="{$name}"
                    {/if}
                    name="name"
                    placeholder="{$c->__('chatrooms.name_placeholder')}"
                    onblur="RoomsUtils_ajaxResolveSlug(this.value)"
                    required />
                <label>{$c->__('chatrooms.name')}</label>
            </div>
        {/if}

        {if="isset($id) && !$create"}
            <input type="hidden" value="{$id}" name="jid"/>
        {else}
            <div>
                <input
                    name="jid"
                    {if="isset($mucservice)"}
                        placeholder="chatroom@{$mucservice->server}"
                    {else}
                        placeholder="chatroom@server.com"
                    {/if}
                    type="email"
                    list="suggestions"
                    oninput="Rooms.suggest()"
                    required />
                <label>{$c->__('chatrooms.id')}</label>
            </div>
        {/if}

        {if="!$create"}
            <div>
                <input
                    {if="isset($conference)"}
                        value="{$conference->name}"
                    {elseif="isset($info)"}
                        value="{$info->name}"
                    {elseif="isset($name)"}
                        value="{$name}"
                    {/if}
                    name="name"
                    placeholder="{$c->__('chatrooms.name_placeholder')}"
                    required />
                <label>{$c->__('chatrooms.name')}</label>
            </div>
        {/if}

        <div>
            <input
                {if="isset($conference) && !empty($conference->nick)"}
                    value="{$conference->nick}"
                {else}
                    value="{$username}"
                {/if}
                name="nick"
                placeholder="{$username}"/>
            <label>{$c->__('chatrooms.nickname')}</label>
        </div>
        <div>
            <div class="select">
                <select name="notify">
                    <option value="never" {if="isset($conference) && $conference->notify == 0"}selected{/if}>
                        {$c->__('room.notify_never')}
                    </option>
                    <option value="quoted" {if="isset($conference) && $conference->notify == 1"}selected{/if}
                        {if="!isset($conference)"}selected{/if}>
                        {$c->__('room.notify_quoted')}
                    </option>
                    <option value="always" {if="isset($conference) && $conference->notify == 2"}selected{/if}>
                        {$c->__('room.notify_always')}
                    </option>
                </select>
            </div>
            <label>{$c->__('room.notify_title')}</label>
        </div>
        <div class="control">
            <ul class="list middle fill">
                <li class="wide">
                    <span class="primary icon gray">
                        <i class="material-icons">login</i>
                    </span>
                    <span class="control">
                        <div class="checkbox">
                            <input
                                {if="isset($conference) && $conference->autojoin"}
                                    checked
                                {/if}
                                type="checkbox"
                                id="autojoin"
                                name="autojoin"/>
                            <label for="autojoin"></label>
                        </div>
                    </span>
                    <div>
                        <p></p>
                        <p class="normal">{$c->__('chatrooms.autojoin')}</p>
                    </div>
                </li>
            </ul>
        </div>
        <div class="control">
            <ul class="list middle fill">
                <li class="wide">
                    <span class="primary icon gray">
                        <i class="material-icons">push_pin</i>
                    </span>
                    <span class="control">
                        <div class="checkbox">
                            <input
                                {if="isset($conference) && $conference->pinned"}
                                    checked
                                {/if}
                                type="checkbox"
                                id="pinned"
                                name="pinned"/>
                            <label for="pinned"></label>
                        </div>
                    </span>
                    <div>
                        <p></p>
                        <p class="normal">{$c->__('chatrooms.pinned')}</p>
                    </div>
                </li>
            </ul>
        </div>
    </section>
    <div class="no_bar">
        <button class="button flat" onclick="Dialog_ajaxClear()">
            {$c->__('button.cancel')}
        </button>
        <button
            class="button flat"
            {if="$create"}
                onclick="RoomsUtils_ajaxAddCreate(MovimUtils.formToJson('bookmarkmucadd'));"
            {else}
                onclick="RoomsUtils_ajaxAddConfirm(MovimUtils.formToJson('bookmarkmucadd'));"
            {/if}
            >
            {if="isset($conference)"}
                {$c->__('button.edit')}
            {else}
                {$c->__('button.add')}
            {/if}
        </button>
    </div>
</div>
