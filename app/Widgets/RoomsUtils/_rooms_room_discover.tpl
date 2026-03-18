<section>
    {if="$room"}
        <ul class="list thick">
            <li>
                <div>
                    <p>{$c->__('rooms.discover')}</p>
                    <p class="line">{$room}</p>
                </div>
            </li>
        </ul>
    {else}
        <h3>{$c->__('rooms.discover')}</h3>
    {/if}

    <form name="rooms_discover">
        {if="$room == null"}
            {if="$gateways->isNotEmpty() && !isset($conference)"}
                {$group = null}
                <div>
                    <div class="select">
                        <select onchange="RoomsUtils_ajaxDiscoGateway(this.value)">
                            <option value="">{$c->__('rooms.default_room')}</option>
                            {loop="$gateways"}
                                {if="$group != $value->parent"}
                                    {if="$group != null"}
                                        </optgroup>
                                    {/if}
                                    <optgroup label="{$value->parent}">
                                {/if}
                                <option value="{$value->server}">
                                    {if="!empty($value->name)"}
                                        {$value->name} -
                                    {/if}
                                    {$value->server}
                                </option>
                                {$group = $value->parent}
                            {/loop}
                            {if="$group != null"}
                                </optgroup>
                            {/if}
                        </select>
                    </div>
                    <label>{$c->__('rooms.type_room')}</label>
                </div>
            {/if}

            <div id="gateway_rooms"></div>

            <datalist id="suggestions">
            </datalist>

            <div id="bookmarkmucadd_jid">
                <ul class="list">
                    <li>
                        <span class="control icon divided gray active" >
                            <i class="material-symbols">search</i>
                        </span>
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
                                oninput="Rooms.cleanId(this); Rooms.suggest(); MovimUtils.addClass('#rooms_discover_add', 'disabled')"
                                onblur="RoomsUtils_ajaxDiscoRoom(this.value)"
                                required />
                            <label>{$c->__('chatrooms.id')}</label>
                        </div>
                    </li>
                </ul>
            </div>
        {else}
            <input type="hidden" name="jid" value="{$room}">
        {/if}
    </form>

    <div id="rooms_discover_result"></div>
</section>
<footer>
    <button onclick="Dialog_ajaxClear()" class="button flat">
        {$c->__('button.cancel')}
    </button>
    <button id="rooms_discover_add"
        onclick="RoomsUtils_ajaxAdd(document.querySelector('form[name=rooms_discover] input[name=jid]').value);"
        class="button color green disabled">
        {$c->__('button.add')}
    </button>
</footer>
