<section>
    <form name="spacerooms_edit">
        <ul class="list">
            <li>
                <span class="primary icon gray">
                    <i class="material-symbols">tag</i>
                </span>
                <span class="control icon gray divided active" onclick="SpaceRooms_ajaxAskDestroy('{$conference->space_server}', '{$conference->space_node}', '{$conference->conference}');">
                    <i class="material-symbols">delete</i>
                </span>
                <div>
                    <p class="line">{$conference->title}</p>
                    {if="$conference->call"}
                        <p>{$c->__('chatrooms.conference_call_edit')}</p>
                    {else}
                        <p>{$c->__('chatrooms.edit')}</p>
                    {/if}
                </div>
            </li>
        </ul>

        <input type="hidden" name="server" value="{$conference->space_server|echapJS}">
        <input type="hidden" name="node" value="{$conference->space_node|echapJS}">
        <input type="hidden" name="conference" value="{$conference->conference|echapJS}">
        <div>
            <ul class="list">
                <li>
                    <span class="primary icon gray">
                        {if="$conference->call"}
                            <i class="material-symbols">adaptive_audio_mic</i>
                        {else}
                            <i class="material-symbols">title</i>
                        {/if}
                    </span>
                    <div>
                        <input
                            name="name"
                            placeholder="{$c->__('chatrooms.name_placeholder')}"
                            value="{$conference->title}"
                            required />
                        <label>{$c->__('general.name')}</label>
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
