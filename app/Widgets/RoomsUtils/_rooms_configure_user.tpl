<section>
    <h3>{$c->__('room.configure_user')}</h3>
    <ul class="list thick">
        <li>
            <span class="control icon active divided" onclick="RoomsUtils_ajaxRemoveMember('{$room->conference}', '{$contact->id}')">
                <i class="material-symbols">delete</i>
            </span>
            <span class="primary icon bubble small {if="$presence"}status {$presence->presencekey}{/if}">
                <img loading="lazy" src="{$contact->getPicture()}">
            </span>
            <div>
                <p>{$contact->truename}</p>
                <p>{$contact->id}</p>
            </div>
        </li>
    </ul>
    {if="$member"}
    <form name="changeaffiliation">
        <input type="hidden" name="jid" value="{$contact->id}"/>
        <div>
            <ul class="list">
                <li>
                    <span class="primary icon gray">
                        <i class="material-symbols">assignment_ind</i>
                    </span>
                    <div>
                        <div class="select">
                            <select type="list-single" label="Maximum Number of Occupants" id="affiliation" name="affiliation"
                                onchange="RoomsUtils_ajaxChangeAffiliationConfirm('{$room->conference}', MovimUtils.formToJson('changeaffiliation'));">
                                <option value="owner" {if="$member->affiliation == 'owner'"}selected{/if} {if="$room->presence && $room->presence->mucaffiliation != 'owner'"}disabled{/if}>
                                    {$c->__('affiliation.owner')}
                                </option>
                                <option value="admin" {if="$member->affiliation == 'admin'"}selected{/if} {if="$room->presence &&     $room->presence->mucaffiliation != 'owner'"}disabled{/if}>
                                    {$c->__('affiliation.admin')}
                                </option>
                                <option value="member" {if="$member->affiliation == 'member'"}selected{/if}>
                                    {$c->__('affiliation.member')}
                                </option>
                            </select>
                        </div>
                        <label for="affiliation">{$c->__('room.role')}</label>
                    </div>
                </li>
            </ul>
        </div>
    </form>
    {/if}
    {if="$presence"}
    <form name="changevoice">
        <div class="control">
            <ul class="list middle">
                <div class="control">
                    <ul class="list fill">
                        <li>
                            <span class="primary icon gray">
                                <i class="material-symbols">voice_selection</i>
                            </span>
                            <span class="control">
                                <div class="checkbox">
                                    <input type="checkbox" id="voice" name="voice" {if="$presence->mucrole != 'visitor'"}checked{/if}
                                    onchange="RoomsUtils_ajaxChangeVoice('{$room->conference}', '{$contact->id}', MovimUtils.formToJson('changevoice'));">
                                    <label for="voice"></label>
                                </div>
                            </span>
                            <p class="all">{$c->__('room.allowed_send_messages')}</p>
                        </li>
                    </ul>
                </div>
            </ul>
        </div>
    </form>
    {/if}
</section>
<footer>
    <button class="button flat oppose" onclick="Dialog_ajaxClear()">
        {$c->__('button.close')}
    </button>
</footer>
