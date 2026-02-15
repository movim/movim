<section>
    <form name="spacerooms_edit">
        <h3>{$c->__('rooms.edit')}</h3>

        <input type="hidden" name="server" value="{$conference->space_server|echapJS}">
        <input type="hidden" name="node" value="{$conference->space_node|echapJS}">
        <input type="hidden" name="conference" value="{$conference->conference|echapJS}">
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
                            value="{$conference->title}"
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
                                {if="$conference->pinned"}checked{/if}
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
        onclick="SpaceRooms_ajaxEdit(MovimUtils.formToJson('spacerooms_edit')); Dialog_ajaxClear();">
            {$c->__('button.edit')}
    </button>
</footer>
