<form id="adminreported_widget_list">
    <div>
        <ul class="list fill thick">
            <li>
                <span class="primary icon gray">
                    <i class="material-icons">info</i>
                </span>
                <div>
                    <p class="line normal">{$c->__('adminreported.info')}</p>
                    <p>{$c->__('adminreported.info2')}</p>
                </div>
            </li>
        </ul>
        <ul class="list fill">
            {loop="$reported"}
                <li id="reported-{$value->id|cleanupId}">
                    <span class="primary">
                        <div class="checkbox">
                            <input
                                type="checkbox"
                                id="adminreported_{$value->id|cleanupId}"
                                name="adminreported_{$value->id|cleanupId}"
                                {if="$value->blocked"}checked{/if}
                                onchange="AdminReported_ajaxBlock('{$value->id|echapJS}', this.checked)"/>
                            <label for="adminreported_{$value->id|cleanupId}"></label>
                        </div>
                    </span>
                    <div>
                        <p class="line normal">
                            <span class="info">{$value->created_at|strtotime|prepareDate}</span>
                            {$value->id}
                        </p>
                        <p class="line" title="{$value->users()->pluck('id')->implode(', ')}">{$c->__('adminreported.reported_by', $value->users()->count())}</p>
                    </div>
                </li>
            {/loop}
        </ul>
    </div>
</form>