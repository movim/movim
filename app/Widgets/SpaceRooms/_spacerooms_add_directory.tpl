<section>
    <ul class="list middle">
        <li>
            <span class="primary icon gray">
                <i class="material-symbols">create_new_folder</i>
            </span>
            <div>
                <h3>{$c->__('spaceinfo.add_directory_title')}</h3>
            </div>
        </li>
    </ul>
    <form name="spacerooms_directory_add">
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
                            name="title"
                            placeholder="{$c->__('chatrooms.name_placeholder')}"
                            required />
                        <label>{$c->__('general.name')}</label>
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
        onclick="SpaceRooms_ajaxAddDirectory(MovimUtils.formToJson('spacerooms_directory_add'));">
            {$c->__('button.add')}
    </button>
</footer>
