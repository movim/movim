<section>
    <ul class="list thick">
        <li>
            <span class="primary icon gray">
                <i class="material-symbols">folder_off</i>
            </span>
            <div>
                <p>{$c->__('spaceinfo.remove_directory_title')}</p>
                <p>{$directory->title}</p>
            </div>
        </li>
    </ul>
    <form name="spacerooms_directory_remove">
        <input type="hidden" name="server" value="{$directory->server|echapJS}">
        <input type="hidden" name="node" value="{$directory->node|echapJS}">
        <input type="hidden" name="directory_id" value="{$directory->id|echapJS}">
    </form>
    <ul class="list">
        <li>
            <span class="primary icon blue">
                <i class="material-symbols">info</i>
            </span>
            <div>
                <p>{$c->__('spaceinfo.remove_directory_confirm')}</p>
                {if="$directory->conferences_count > 0"}
                    <p>{$c->__('spaceinfo.remove_directory_text', $directory->conferences_count)}</p>
                {/if}
            </div>
        </li>
    </ul>
</section>
<footer>
    <button class="button flat" onclick="Dialog_ajaxClear()">
        {$c->__('button.cancel')}
    </button>
    <button
        class="button flat color red"
        onclick="SpaceRooms_ajaxDestroyDirectory(MovimUtils.formToJson('spacerooms_directory_remove')); Dialog_ajaxClear()">
            {$c->__('button.delete')}
    </button>
</footer>
