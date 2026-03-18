<section>
    {if="isset($id)"}
        <ul class="list thick">
            <li>
                <div>
                    {if="$create"}
                        <p>{$c->__('rooms.create')}</p>
                    {else}
                        {if="isset($conference)"}
                            <p>{$c->__('rooms.edit')}</p>
                        {else}
                            <p>{$c->__('rooms.join')}</p>
                        {/if}
                    {/if}

                    <p>{$id}</p>
                </div>
            </li>
        </ul>
    {else}
        <h3>{$c->__('rooms.create')}</h3>
    {/if}

    <form name="bookmarkmucadd">
        {if="$create"}
            <div>
                <ul class="list middle">
                    <li>
                        <span class="primary icon gray">
                            <i class="material-symbols">people_alt</i>
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
                            <i class="material-symbols">wifi_tethering</i>
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
            <div id="bookmarkmucadd_jid">
                <input
                    name="jid"
                    {if="isset($mucservice)"}
                        placeholder="chatroom@{$mucservice->server}"
                    {else}
                        placeholder="chatroom@server.com"
                    {/if}
                    type="email"
                    list="suggestions"
                    oninput="Rooms.cleanId(this); Rooms.suggest()"
                    required />
                <label>{$c->__('chatrooms.id')}</label>
            </div>
        {/if}

        <div>
            <ul class="list">
                {if="!$create"}
                    <li>
                        <span class="primary icon gray">
                            <i class="material-symbols">short_text</i>
                        </span>
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
                    </li>
                {/if}

                <li>
                    <span class="primary icon gray">
                        <i class="material-symbols">person_book</i>
                    </span>
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
                </li>
                <li>
                    <span class="primary icon gray">
                        <i class="material-symbols">edit_notifications</i>
                    </span>
                    <div>
                        <div class="select">
                            <select name="notify">
                                <option value="never" {if="isset($conference) && $conference->notify == 0"}selected{/if}>
                                    {$c->__('room.notify_never')}
                                </option>
                                <option value="on-mention" {if="isset($conference) && $conference->notify == 1"}selected{/if}
                                    {if="!isset($conference)"}selected{/if}>
                                    {$c->__('room.notify_mentioned')}
                                </option>
                                <option value="always" {if="isset($conference) && $conference->notify == 2"}selected{/if}>
                                    {$c->__('room.notify_always')}
                                </option>
                            </select>
                        </div>
                        <label>{$c->__('room.notify_title')}</label>
                    </div>
                </li>
                <li>
                    <span class="primary icon gray">
                        <i class="material-symbols">login</i>
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
                        <p class="all">{$c->__('chatrooms.autojoin')}</p>
                    </div>
                </li>
                <li>
                    <span class="primary icon gray">
                        <i class="material-symbols">push_pin</i>
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
                        <p>{$c->__('chatrooms.pinned')}</p>
                    </div>
                </li>
            </ul>
        </div>
    </form>
</section>
<footer>
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
</footer>
