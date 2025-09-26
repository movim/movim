<section>
    <ul class="list thick">
        <li>
            <span class="primary icon bubble small {if="$presence"}status {$presence->presencekey}{/if}">
                <img loading="lazy" src="{$contact->getPicture()}">
            </span>
            <div>
                <p>{$c->__('room.configure_user')}</p>
                <p>{$contact->id}</p>
            </div>
        </li>
    </ul>
    {if="$presence"}
    <form name="changerole">
        <div class="control">
            <ul class="list thin">
                <li class="subheader">
                    <div>
                        <p>{$c->__('room.role')}</p>
                    </div>
                    <span class="chip active color green"
                        onclick="RoomsUtils_ajaxChangeRole('{$room->conference}', '{$contact->id}', MovimUtils.formToJson('changerole')); Dialog_ajaxClear();">
                        <i class="material-symbols">check</i>
                        {$c->__('button.save')}
                    </span>
                </li>
                <li>
                    <span class="primary icon gray">
                        <i class="material-symbols">server_person</i>
                    </span>
                    <div>
                        <div class="select">
                            <select type="list-single" label="{$c->__('room.role')}" id="role" name="role">
                                <option value="participant" {if="$presence && $presence->mucrole == 'participant'"}selected{/if}>{$c->__('room.role_participant')}</option>
                                <option value="moderator" {if="$presence && $presence->mucrole == 'moderator'"}selected{/if}>{$c->__('room.role_moderator')}</option>
                                <option value="visitor" {if="$presence->mucrole && $presence->mucrole == 'visitor'"}selected{/if}>{$c->__('room.role_visitor')}</option>
                            </select>
                        </div>
                        <label for="affiliation">{$c->__('room.role')}</label>
                    </div>
                </li>
            </ul>
        </div>
    </form>
    {/if}
    <form name="changeaffiliation">
        <input type="hidden" name="jid" value="{$contact->id}"/>
        <div>
            <ul class="list thin">
                <li class="subheader">
                    <div>
                        <p>{$c->__('room.affiliation')}</p>
                    </div>
                    <span class="chip active color green" onclick="RoomsUtils_ajaxChangeAffiliationConfirm('{$room->conference}', MovimUtils.formToJson('changeaffiliation')); Dialog_ajaxClear();">
                        <i class="material-symbols">check</i>
                        {$c->__('button.save')}
                    </span>
                </li>
                <li>
                    <span class="primary icon gray">
                        <i class="material-symbols">assignment_ind</i>
                    </span>
                    <div>
                        <div class="select">
                            <select type="list-single" label="Maximum Number of Occupants" id="affiliation" name="affiliation">
                                <option value="owner" {if="$member && $member->affiliation == 'owner'"}selected{/if}>{$c->__('room.affiliation_owner')}</option>
                                <option value="admin" {if="$member && $member->affiliation == 'admin'"}selected{/if}>{$c->__('room.affiliation_admin')}</option>
                                <option value="member" {if="$member && $member->affiliation == 'member'"}selected{/if}>{$c->__('room.affiliation_member')}</option>
                                <option value="none" {if="$member && $member->affiliation == 'none'"}selected{/if}>{$c->__('room.affiliation_none')}</option>
                            </select>
                        </div>
                        <label for="affiliation">{$c->__('room.affiliation')}</label>
                    </div>
                </li>
                <li>
                    <span class="primary icon gray">
                        <i class="material-symbols">short_text</i>
                    </span>
                    <div>
                        <textarea name="reason" placeholder="{$c->__('room.reason')}" data-autoheight="true"></textarea>
                        <label for="reason">{$c->__('room.reason')} ({$c->__('input.optional')})</label>
                    </div>
                </li>
            </ul>
        </div>
    </form>
</section>
<footer>
    <button class="button flat oppose" onclick="Dialog_ajaxClear()">
        {$c->__('button.close')}
    </button>
</footer>
