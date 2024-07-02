<section>
    <form name="changeaffiliation">
        <h3>{$c->__('room.change_affiliation')}</h3>

        <br />
        <h4 class="gray">{$jid}</h4>
        <input type="hidden" name="jid" value="{$jid}"/>

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

        <div>
            <textarea name="reason" placeholder="{$c->__('room.reason')}" data-autoheight="true"></textarea>
            <label for="reason">{$c->__('room.reason')} ({$c->__('input.optional')})</label>
        </div>
    </form>
</section>
<div>
    <div>
        <button class="button flat" onclick="Dialog_ajaxClear()">
            {$c->__('button.close')}
        </button>
        <button
            class="button flat"
            onclick="RoomsUtils_ajaxChangeAffiliationConfirm('{$room->conference}', MovimUtils.formToJson('changeaffiliation')); Dialog_ajaxClear();">
            {$c->__('button.submit')}
        </button>
    </div>
</div>
