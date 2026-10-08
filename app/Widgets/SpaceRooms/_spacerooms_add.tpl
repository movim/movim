<section>
    <ul class="list thick">
        <li>
            <span class="primary icon gray">
                {if="$directory"}
                    <i class="material-symbols">folder</i>
                {else}
                    <i class="material-symbols">tag</i>
                {/if}
            </span>
            <div>
                {if="$directory"}
                    <p>{$c->__('spaceinfo.add_room_title')}</p>
                    <p>{$directory->title}</p>
                {else}
                    <h3>{$c->__('spaceinfo.add_room_title')}</h3>
                {/if}
            </div>
        </li>
    </ul>
    <form name="spacerooms_add">

        <input type="hidden" name="server" value="{$server|echapJS}">
        <input type="hidden" name="node" value="{$node|echapJS}">
        {if="$directory"}
            <input type="hidden" name="directory_id" value="{$directory->id|echapJS}">
        {/if}
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
                        <label>{$c->__('general.name')}</label>

                    </div>
                </li>
                <li>
                    <span class="primary icon gray">
                        <i class="material-symbols">adaptive_audio_mic</i>
                    </span>
                    <span class="control">
                        <div class="checkbox">
                            <input
                                type="checkbox"
                                id="call"
                                name="call"/>
                            <label for="call"></label>
                        </div>
                    </span>
                    <div>
                        <p>{$c->__('chatrooms.conference_call')}</p>
                        <p>{$c->__('chatrooms.conference_call_text')}</p>
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
        onclick="SpaceRooms_ajaxAdd(MovimUtils.formToJson('spacerooms_add'));">
            {$c->__('button.add')}
    </button>
</footer>
