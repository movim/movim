<section>
    <form name="spacerooms_add">
        <h3>{$c->__('rooms.create')}</h3>

        <input type="hidden" name="server" value="{$server|echapJS}">
        <input type="hidden" name="node" value="{$node|echapJS}">
        <div>
            <ul class="list">
                <li>
                    <span class="primary icon gray">
                        <i class="material-symbols">short_text</i>
                    </span>
                    <div>
                        <input
                            name="name"
                            placeholder="{$c->__('chatrooms.name_placeholder')}"
                            required />
                        <label>{$c->__('chatrooms.name')}</label>

                    </div>
                </li>
                <li>
                    <span class="primary icon gray">
                        <i class="material-symbols">push_pin</i>
                    </span>
                    <span class="control">
                        <div class="checkbox">
                            <input
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
        onclick="SpaceRooms_ajaxAddCreate(MovimUtils.formToJson('spacerooms_add')); Dialog_ajaxClear();">
            {$c->__('button.add')}
    </button>
</footer>
