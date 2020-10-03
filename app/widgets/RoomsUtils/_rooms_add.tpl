<section>
    <form name="bookmarkmucadd">
        {if="isset($conference)"}
            <h3>{$c->__('rooms.edit')}</h3>
        {elseif="isset($id)"}
            <h3>{$c->__('rooms.join')}</h3>
        {else}
            <h3>{$c->__('rooms.create_or_join')}</h3>
        {/if}

        {if="isset($id)"}
            <h4>{$id}</h4>
        {/if}

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
                {if="!isset($id)"}
                    onblur="RoomsUtils_ajaxResolveSlug(this.value)"
                {/if}
                required />
            <label>{$c->__('chatrooms.name')}</label>
        </div>

        {if="isset($id)"}
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
                    <option value="quoted" {if="isset($conference) && $conference->notify == 1"}selected{/if}>
                        {$c->__('room.notify_quoted')}
                    </option>
                    <option value="always" {if="isset($conference) && $conference->notify == 2"}selected{/if}>
                        {$c->__('room.notify_always')}
                    </option>
                </select>
            </div>
            <label>{$c->__('room.notify_title')}</label>
        </div>
        <div>
            <ul class="list thick fill">
                <li class="wide">
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
    </section>
    <div class="no_bar">
        <button class="button flat" onclick="Dialog_ajaxClear()">
            {$c->__('button.cancel')}
        </button>
        <button
            class="button flat"
            onclick="RoomsUtils_ajaxAddConfirm(MovimUtils.formToJson('bookmarkmucadd'));">
            {if="isset($conference)"}
                {$c->__('button.edit')}
            {else}
                {$c->__('button.add')}
            {/if}
        </button>
    </div>
</div>
