<div class="tabelem padded_top_bottom" title="{$c->__('blocked.title')}" data-mobileicon="block" id="blocked_widget">
    <ul class="list fill thick">
        <li>
            <span class="primary icon gray">
                <i class="material-icons">info</i>
            </span>
            <div>
                <p class="line normal">{$c->__('blocked.info')}</p>
                <p class="line">{$c->__('blocked.info2')}</p>
            </div>
        </li>
    </ul>
    <ul class="list fill thin" id="blocked_widget_list">{loop="$blocked"}<li id="blocked-{$value->id|cleanupId}">
            <span class="primary icon gray">
                <i class="material-icons">person</i>
            </span>
            <span class="control icon active divided"
                onclick="Blocked_ajaxUnblockContact('{$value->id|echapJS}')">
                <i class="material-icons">close</i>
            </span>
            <div>
                <p class="line normal">
                    <span class="info">{$value->created_at|strtotime|prepareDate}</span>
                    {$value->id}
                </p>
            </div>
        </li>{/loop}</ul>
    <div class="placeholder">
        <i class="material-icons">block</i>
        <h4>{$c->__('blocked.placeholder')}</h4>
    </div>
</div>
