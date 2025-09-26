<section>
    <h3>{$c->__('room.configure_user')}</h3>
    <ul class="list thick">
        <li>
            <span class="primary icon bubble small {if="$presence"}status {$presence->presencekey}{/if}">
                <img loading="lazy" src="{$contact->getPicture()}">
            </span>
            <div>
                <p>{$contact->truename}</p>
                <p>{$contact->id}</p>
            </div>
        </li>
    </ul>
    <form name="changeaffiliation">
        <input type="hidden" name="jid" value="{$contact->id}"/>
        <div>
            <ul class="list thin">
                <li>
                    <span class="primary icon gray">
                        <i class="material-symbols">assignment_ind</i>
                    </span>
                    <div>
                        <div class="select">
                            <select type="list-single" label="Maximum Number of Occupants" id="affiliation" name="affiliation"
                                onchange="RoomsUtils_ajaxChangeAffiliationConfirm('{$room->conference}', MovimUtils.formToJson('changeaffiliation'));">
                                <option value="owner" {if="$member && $member->affiliation == 'owner'"}selected{/if}>{$c->__('room.affiliation_owner')}</option>
                                <option value="admin" {if="$member && $member->affiliation == 'admin'"}selected{/if}>{$c->__('room.affiliation_admin')}</option>
                                <option value="member" {if="$member && $member->affiliation == 'member'"}selected{/if}>{$c->__('room.affiliation_member')}</option>
                                <option value="none" {if="$member && $member->affiliation == 'none'"}selected{/if}>{$c->__('room.affiliation_none')}</option>
                            </select>
                        </div>
                        <label for="affiliation">{$c->__('room.role')}</label>
                    </div>
                </li>
            </ul>
        </div>
    </form>
    {if="$presence"}
    <form name="changevoice">
        <div class="control">
            <ul class="list thin">
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
                            <p class="normal all">{$c->__('room.allowed_send_messages')}</p>
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
