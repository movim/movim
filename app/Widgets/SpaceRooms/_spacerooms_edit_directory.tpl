<section>
    <ul class="list thick">
        <li>
            <span class="primary icon gray">
                <i class="material-symbols">folder</i>
            </span>
            <span class="control icon gray active" onclick="SpaceRooms_ajaxAskDestroyDirectory('{$directory->server}', '{$directory->node}', '{$directory->id}')">
                <i class="material-symbols">delete</i>
            </span>
            <div>
                <p>{$c->__('spaceinfo.edit_directory_title')}</p>
                <p>{$directory->title}</p>
            </div>
        </li>
    </ul>
    <form name="spacerooms_directory_edit">

        <input type="hidden" name="server" value="{$directory->server|echapJS}">
        <input type="hidden" name="node" value="{$directory->node|echapJS}">
        <input type="hidden" name="directory_id" value="{$directory->id|echapJS}">
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
                            value="{$directory->title|echapJS}"
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
        onclick="SpaceRooms_ajaxEditDirectory(MovimUtils.formToJson('spacerooms_directory_edit')); Dialog_ajaxClear()">
            {$c->__('button.edit')}
    </button>
</footer>
