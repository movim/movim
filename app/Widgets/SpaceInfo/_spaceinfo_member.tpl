<section>
    <h3>{$c->__('spaceinfo.config_title')}</h3>
    <form name="spaceinfo_member" onchange="SpaceInfo_ajaxChangeConfiguration('{$subscription->server}', '{$subscription->node}', MovimUtils.formToJson('spaceinfo_member'));">
        <div>
            <ul class="list">
                <li>
                    <span class="primary icon gray">
                        <i class="material-symbols">edit_notifications</i>
                    </span>
                    <div>
                        <div class="select">
                            <select name="notify">
                                <option value="never" {if="$subscription->notify == 'never'"}selected{/if}>
                                    {$c->__('room.notify_never')}
                                </option>
                                <option value="on-mention" {if="$subscription->notify == 'on-mention'"}selected{/if}>
                                    {$c->__('room.notify_mentioned')}
                                </option>
                                <option value="always" {if="$subscription->notify == 'always'"}selected{/if}>
                                    {$c->__('room.notify_always')}
                                </option>
                            </select>
                        </div>
                        <label>{$c->__('room.notify_title')}</label>
                    </div>
                </li>
                <li>
                    <span class="primary icon gray">
                        <i class="material-symbols">push_pin</i>
                    </span>
                    <span class="control">
                        <div class="checkbox">
                            <input
                                {if="$subscription->pinned"}
                                    checked
                                {/if}
                                type="checkbox"
                                id="pinned"
                                name="pinned"/>
                            <label for="pinned"></label>
                        </div>
                    </span>
                    <div>
                        <p>{$c->__('spaceinfo.pinned')}</p>
                    </div>
                </li>
            </ul>
        </div>
    </form>

    <br />
    <hr />

    <ul class="list divided middle active {if="$affiliation?->affiliation == 'owner'"}disabled{/if}">
        <li onclick="SpacesMenu_ajaxLeaveMenu('{$subscription->server}', '{$subscription->node}')">
            <span class="primary icon gray">
                <i class="material-symbols">door_open</i>
            </span>
            <span class="control icon">
                <i class="material-symbols">chevron_forward</i>
            </span>
            <div>
                <p class="line">{$c->__('spaceinfo.leave_title')}</p>
                {if="$affiliation?->affiliation == 'owner'"}
                    <p>{$c->__('spaceinfo.owner_cannot_leave')}</p>
                {/if}
            </div>
        </li>
    </ul>
</section>
<footer>
    <button class="button flat" onclick="Dialog_ajaxClear()">
        {$c->__('button.cancel')}
    </button>
</footer>
